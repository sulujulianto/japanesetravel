<select {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm text-slate-800 shadow-sm transition focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/20 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-100']) }}>
    {{ $slot }}
</select>
