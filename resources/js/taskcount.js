import { addWorkSeconds, incrementCompletedTasks } from './dailyreport.js';

// タスクカウントの初期化を行う
export function initTaskCount() {
    const root = document.getElementById('task-countdown-minute-root');
    if (!root) {
        return;
    }
    // タスクカウントの表示を取得する
    const display = root.querySelector('#task-countdown-display');
    // タスクカウントの開始ボタンを取得する（ストレッチカード・作業タイマーカードの両方）
    const startBtns = [
        root.querySelector('#start-task-countdown-minute'),
        document.getElementById('start-stretch-countdown'),
    ].filter(Boolean);
    // タスクカウントのボタンを定義する(のちのカウントダウンに生かすため)
    const taskButtons = root.querySelectorAll('.task-item');
    // タスクカウントの表示または、開始ボタンがない場合は終了する
    if (!display || startBtns.length === 0) {
        return;
    }

    function setStartButtonsDisabled(disabled) {
        startBtns.forEach((button) => {
            button.disabled = disabled;
        });
    }
    // タスクカウントのデータをJSON形式で取得する
    const tasks = JSON.parse(root.dataset.tasks ?? '[]');
    // タスクカウントのIDを定義   
    const checkedTaskIds = new Set();
    // タスクカウントの時間を計算しやすいように定義しておく
    let remainingSeconds = 0;
    let activeTaskId = null;
    let intervalId = null;

    // 時間を定義する
    function formatTime(seconds) {
        const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    // タスクカウントの次の未チェックのタスクのIDをconfig/task.phpから取得する
    function getNextUncheckedTaskId() {
        for (const task of tasks) {
            if (!checkedTaskIds.has(task.id)) {
                return task.id;
            }
        }

        return null;
    }

    // タスクカウントのタスクのボタンを引数taskIdを基に取得する
    function getTaskButton(taskId) {
        return root.querySelector(`.task-item[data-task-id="${taskId}"]`);
    }

    // タスクカウントのタスクのボタンを描画する
    function renderTasks() {
        taskButtons.forEach((button) => {
            const taskId = Number(button.dataset.taskId);
            // タスクカウントのタスクがチェックされているかどうかを判定する
            const isChecked = checkedTaskIds.has(taskId);
            // タスクカウントのタスクがアクティブかどうかを判定する
            const isActive = activeTaskId === taskId;
            // タスクカウントのタスクのボタンのクラスを切り替える
            button.classList.toggle('is-checked', isChecked);
            button.classList.toggle('is-active', isActive);
            button.disabled = intervalId !== null && !isChecked && !isActive;
        });
        // タスクカウントがない場合は開始ボタンを無効化する
        if (intervalId === null) {
            setStartButtonsDisabled(getNextUncheckedTaskId() === null);
        }
    }

    // タスクカウントの残り時間を描画する
    function renderCountdown() {
        display.textContent = formatTime(remainingSeconds);
    }

    // タスクカウントのタスクのボタンをクリックした時の処理を行う
    function toggleTask(taskId) {
        // タスクカウントが実行中かどうかを判定する
        if (intervalId !== null && activeTaskId === taskId) {
            return;
        }
        // checkedTaskIdsにtaskIdが存在する場合は削除する
        if (checkedTaskIds.has(taskId)) {
            checkedTaskIds.delete(taskId);
        } else {
            checkedTaskIds.add(taskId);
        }

        renderTasks();
    }
     // カウントダウンが終了した後の処理を定義
    function finishCountdown() {
        clearInterval(intervalId);
        intervalId = null;
         // タスクカウントがあった場合の処理を定義する
        if (activeTaskId !== null) {
            const completedButton = getTaskButton(activeTaskId);
            const completedSeconds = Number(completedButton?.dataset.countdownSeconds ?? 0);
            addWorkSeconds(completedSeconds, 'stretch');
            checkedTaskIds.add(activeTaskId);
        }

        activeTaskId = null;
        remainingSeconds = 0;
        setStartButtonsDisabled(false);
        renderCountdown();
        renderTasks();

        const nextTaskId = getNextUncheckedTaskId();
        if (nextTaskId === null) {
            incrementCompletedTasks();
            return;
        }

        startCountdownForTask(nextTaskId);
    }

    // タスクカウントのスタートボタンをクリックした時の処理を定義する
    function startCountdownForTask(taskId) {
        const button = getTaskButton(taskId);
        if (!button) {
            return;
        }

        activeTaskId = taskId;
        remainingSeconds = Number(button.dataset.countdownSeconds);
        setStartButtonsDisabled(true);
        renderTasks();
        renderCountdown();

        intervalId = setInterval(() => {
            remainingSeconds--;

            if (remainingSeconds <= 0) {
                remainingSeconds = 0;
                finishCountdown();
                return;
            }

            renderCountdown();
        }, 1000);
    }

    // タスクカウントの開始ボタンをクリックした時の処理を行う
    function startCountdown() {
        if (intervalId !== null) {
            return;
        }

        const nextTaskId = getNextUncheckedTaskId();
        if (nextTaskId === null) {
            return;
        }

        startCountdownForTask(nextTaskId);
    }
    // タスクカウントのタスクのボタンを手動でクリックした時の処理を定義する
    taskButtons.forEach((button) => {
        button.addEventListener('click', () => {
            toggleTask(Number(button.dataset.taskId));
        });
    });

    startBtns.forEach((button) => {
        button.addEventListener('click', startCountdown);
    });

    renderCountdown();
    renderTasks();
}
