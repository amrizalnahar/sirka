<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150']) }} style="background-color: #1A6FAA;" onmouseover="this.style.backgroundColor='#124E7A'" onmouseout="this.style.backgroundColor='#1A6FAA'" onfocus="this.style.backgroundColor='#124E7A'" onactive="this.style.backgroundColor='#124E7A'">
    {{ $slot }}
</button>
