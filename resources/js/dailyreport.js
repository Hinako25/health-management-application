let chartRoot = null;
let chartCanvas = null;
let chartCtx = null;

// flowchart.barがないためJSで定義
// グラフのデータを保存するためのキーを定義
function getStorageKey() {
    if (!chartRoot) {
        return 'daily-chart-data';
    }

    const userId = chartRoot.dataset.userId ?? 'guest';
    return `daily-chart-data-${userId}`;
}
// YYYY-MM-DDという形式のキーを作る
function getTodayKey() {
    return new Date().toISOString().slice(0, 10);
}
//migrationから読み込んだ今日のグラフのデータを取得する
function getChartData() {
    if (!chartRoot) {
        return {};
    }

    try {
        // ユーザーのデータを永遠に保存する
        const stored = localStorage.getItem(getStorageKey());
        if (stored) {
            return JSON.parse(stored);
        }
    } catch(error) {
        // データの取得に失敗した場合
        alert('エラーが発生しデータの取得に失敗しました。' + error.message);
        return {};
    }

    try {
        // データの取得に失敗した場合
        alert.error('エラーが発生しデータの取得に失敗しました。もう一度行ってください');
        return JSON.parse(chartRoot.dataset.chartData ?? '{}');
    } catch(error) {
        // データの取得に失敗した場合
        console.error('エラーが発生しデータの取得に失敗しました。' + error.message);
        return {};
    }

}
// グラフのデータを保存する
function saveChartData(data) {
    localStorage.setItem(getStorageKey(), JSON.stringify(data));
}
// 経過した日数を数値化してＹ軸の範囲を決める
export function getElapsedDays() {
    if (!chartRoot) {
        return 1;
    }
    // ユーザーの作成日から経過した日数を数値化してy軸の範囲を決める(createdAtはmigrationのデータ)
    const createdAt = Number(chartRoot.dataset.createdAt);
    const elapsedDays = Math.floor(( new Date().getTime() / 1000 - createdAt) / 86400);

    return Math.max(elapsedDays + 1, 1);
}

// X軸の日付を決める（作成日から total_goals 日分）
function getDayKeys() {
    if (!chartRoot) {
        return [getTodayKey()];
    }

    const totalGoals = Number(chartRoot.dataset.totalGoals);
    const createdAt = Number(chartRoot.dataset.createdAt);

    if (!totalGoals || !createdAt) {
        return [getTodayKey()];
    }

    const start = new Date(createdAt * 1000);
    start.setHours(0, 0, 0, 0);

    const end = new Date(start);
    end.setDate(end.getDate() + totalGoals - 1);

    // 一日をｘ軸の１として書き加えていく(今日の日付は含まない)
    const days = [];
    const daysloop = new Date(start);
    while (daysloop <= end) {
        days.push(daysloop.toISOString().slice(0, 10));
        daysloop.setDate(daysloop.getDate() + 1);
    }

    return days.length > 0 ? days : [getTodayKey()];
}
// 日付ごとの作業時間の合計を定義
function getDayTotalSeconds(entry) {
    if (!entry) {
        return 0;
    }

    if (typeof entry === 'number') {
        return entry;
    }

    return (entry.work ?? 0) + (entry.stretch ?? 0);
}

// 時間の表示を定義
function formatDuration(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);

    if (h > 0) {
        return `${h}時間${m}分`;
    }

    return `${m}分`;
}
// 成果物の表示を更新する
function updateGoalsCard() {
    const goalsRoot = document.getElementById('goals-card-root');
    if (!goalsRoot) {
        return;
    }
    // 成果物の表示を更新する
    const completedEl = goalsRoot.querySelector('#goals-completed-display');
    const totalTimeEl = goalsRoot.querySelector('#goals-total-time-display');
    const dailyTasks = Number(goalsRoot.dataset.dailyTasks ?? 0);
    const completedTasks = Number(goalsRoot.dataset.completedTasks ?? 0);
    const todayTotal = getDayTotalSeconds(getChartData()[getTodayKey()]);
    //成果物の表示を更新する
    if (completedEl) {
        completedEl.textContent = `${completedTasks}/${dailyTasks}`;
    }

    if (totalTimeEl) {
        totalTimeEl.textContent = formatDuration(todayTotal);
    }
}

// 紐づけして成果物のをセットする
export function setCompletedTasksCount(count) {
    const goalsRoot = document.getElementById('goals-card-root');
    const taskRoot = document.getElementById('task-countdown-minute-root');

    if (goalsRoot) {
        goalsRoot.dataset.completedTasks = String(count);
    }

    if (taskRoot) {
        taskRoot.dataset.completedTasks = String(count);
    }

    updateGoalsCard();
}

// 成果物の表示を永続化する
async function persistCompletedTasks(count) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const response = await fetch('/home/completed-tasks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            // JSON形式でデータを送信する
            body: JSON.stringify({ completed_tasks: count }),
        }); 
        if (!response.ok) {
            throw new Error('保存に失敗しました');
        }
    } catch (error) {
        console.error(error);
    }
}
// import先home.jsからhome.blade.php経由で結果を更新する
export async function incrementCompletedTasks() {
    const goalsRoot = document.getElementById('goals-card-root');
    const taskRoot = document.getElementById('task-countdown-minute-root');
    const current = Number(
        goalsRoot?.dataset.completedTasks ?? taskRoot?.dataset.completedTasks ?? 0,
    );
    const next = current + 1;

    setCompletedTasksCount(next);
    await persistCompletedTasks(next);
}

// グラフの描画を行う
function drawChart() {
    if (!chartCanvas || !chartCtx || !chartRoot) {
        return;
    }

    const data = getChartData();
    const days = getDayKeys();
    const values = days.map((day) => getDayTotalSeconds(data[day]));
    const maxVal = Math.max(...values, 60);

    const dpr = window.devicePixelRatio || 1;
    const rect = chartCanvas.getBoundingClientRect();
    chartCanvas.width = Math.max(rect.width, 1) * dpr;
    chartCanvas.height = Math.max(rect.height, 1) * dpr;
    chartCtx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const width = rect.width;
    const height = rect.height;
    const padding = { top: 12, right: 8, bottom: 20, left: 32 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    chartCtx.clearRect(0, 0, width, height);
    chartCtx.fillStyle = '#f9fafb';
    chartCtx.fillRect(padding.left, padding.top, chartWidth, chartHeight);

    const slotWidth = chartWidth / days.length;
    const barWidth = Math.max(slotWidth * 0.6, 2);
    const labelStep = days.length > 60 ? 10 : days.length > 30 ? 5 : days.length > 14 ? 2 : 1;

    values.forEach((value, index) => {
        const barHeight = (value / maxVal) * chartHeight;
        const x = padding.left + index * slotWidth + (slotWidth - barWidth) / 2;
        const y = padding.top + chartHeight - barHeight;

        chartCtx.fillStyle = '#22c55e';
        chartCtx.globalAlpha = value > 0 ? 0.85 : 0.2;
        chartCtx.fillRect(x, y, barWidth, Math.max(barHeight, value > 0 ? 2 : 0));
        chartCtx.globalAlpha = 1;

        if (index % labelStep === 0 || index === days.length - 1) {
            chartCtx.fillStyle = '#6b7280';
            chartCtx.font = '9px sans-serif';
            chartCtx.textAlign = 'center';
            chartCtx.fillText(String(index + 1), x + barWidth / 2, height - 6);
        }
    });

    chartCtx.strokeStyle = '#d1d5db';
    chartCtx.beginPath();
    chartCtx.moveTo(padding.left, padding.top);
    chartCtx.lineTo(padding.left, padding.top + chartHeight);
    chartCtx.lineTo(padding.left + chartWidth, padding.top + chartHeight);
    chartCtx.stroke();

    chartCtx.fillStyle = '#6b7280';
    chartCtx.font = '10px sans-serif';
    chartCtx.textAlign = 'right';
    chartCtx.fillText(formatDuration(maxVal), padding.left - 4, padding.top + 10);

    const summary = chartRoot.querySelector('#daily-chart-summary');
    if (summary) {
        const todayTotal = getDayTotalSeconds(data[getTodayKey()]);
        summary.textContent = `今日の作業時間: ${formatDuration(todayTotal)}`;
    }
}

// 作業時間の計測を始める
export function addWorkSeconds(seconds, type = 'work') {
    if (seconds <= 0) {
        return;
    }

    const data = getChartData();
    const today = getTodayKey();
    const completedTasks = Number(goalsRoot?.dataset.completedTasks ?? taskRoot?.dataset.completedTasks ?? 0);

    if (!data[today] || typeof data[today] === 'number') {
        const previous = typeof data[today] === 'number' ? data[today] : 0;
        data[today] = { work: previous, stretch: 0 };
    }

    data[today][type] = (data[today][type] ?? 0) + seconds;
    saveChartData(data);
    updateDailyChart();
}

// グラフの描写をhome.jsに送り、更新をする
export function updateDailyChart() {
    drawChart();
    updateGoalsCard();
}

// グラフの初期化をhome.jsに送り、結果を更新する
export function initDailyReport() {
    chartRoot = document.getElementById('daily-chart-root');
    updateGoalsCard();

    if (!chartRoot) {
        return;
    }

    chartCanvas = chartRoot.querySelector('#daily-chart-canvas');
    if (!chartCanvas) {
        return;
    }

    chartCtx = chartCanvas.getContext('2d');
    updateDailyChart();
    updateGoalsCard();

    if (typeof ResizeObserver !== 'undefined') {
        const observer = new ResizeObserver(() => updateDailyChart());
        observer.observe(chartCanvas);
    } else {
        window.addEventListener('resize', updateDailyChart);
    }
}
