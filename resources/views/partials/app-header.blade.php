<flux:tab.group class="fixed top-0 right-0 left-0 z-10 min-h-10 dark:bg-black">
   @csrf
   <form method="POST" action="{{ route('logout') }}" class="w-full flex items-center">
     <img src="{{ asset('img/logo.png') }}" alt="logo" class="w-10 h-10 inline-block">
     <flux:tabs>
        <flux:tab name="home" icon="home" href="{{ route('home') }}" :current="request()->routeIs('home')">Home</flux:tab>
        <flux:tab name="setting"icon="cog-6-tooth" href="{{ route('dashboard') }}" :current="request()->routeIs('dashboard')">Setting</flux:tab>
        <button name="logout" type="submit" class="flex items-center gap-2 text-sm font-bold text-gray-500 opacity-75 hover:text-gray-600 dark:text-gray-300">
          <flux:icon name="arrow-right-start-on-rectangle" class="w-4 h-4 font-bold flex justify-center text-gray-500 opacity-75 hover:text-gray-600 dark:text-gray-300" />
          Logout
         </button>
     </flux.tabs>
    </form>
</flux:tab.group>