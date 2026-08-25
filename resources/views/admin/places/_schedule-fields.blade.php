<fieldset class="md:col-span-2">
    <legend class="{{ $labelClass }}">{{ __('Hari Buka') }}</legend>
    <div class="mt-2 grid gap-4 sm:grid-cols-2">
        @foreach (['open_day_start' => __('Dari Hari'), 'open_day_end' => __('Sampai Hari')] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-xs font-semibold text-[#526071] dark:text-[#AEB8C7]">{{ $label }}</label>
                <select id="{{ $field }}" name="{{ $field }}" class="{{ $inputClass }}">
                    <option value="">{{ __('Pilih hari') }}</option>
                    @foreach ($scheduleOptions['days'] as $day)
                        <option value="{{ $day['value'] }}" @selected((string) old($field, $scheduleValues[$field]) === $day['value'])>{{ $day['label'] }}</option>
                    @endforeach
                </select>
                @error($field)<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</fieldset>

@foreach (['start' => __('Jam Mulai'), 'end' => __('Jam Selesai')] as $timeKey => $label)
    <fieldset>
        <legend class="{{ $labelClass }}">{{ $label }}</legend>
        <div class="mt-2 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)] gap-2">
            @php
                $hourField = "open_time_{$timeKey}_hour";
                $minuteField = "open_time_{$timeKey}_minute";
                $periodField = "open_time_{$timeKey}_period";
            @endphp
            <div>
                <label for="{{ $hourField }}" class="sr-only">{{ __('Jam') }}</label>
                <select id="{{ $hourField }}" name="{{ $hourField }}" aria-label="{{ __('Jam') }}" class="{{ $inputClass }}">
                    <option value="">{{ __('Jam') }}</option>
                    @foreach ($scheduleOptions['hours'] as $hour)
                        <option value="{{ $hour }}" @selected((string) old($hourField, $scheduleValues[$hourField]) === (string) $hour)>{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="{{ $minuteField }}" class="sr-only">{{ __('Menit') }}</label>
                <select id="{{ $minuteField }}" name="{{ $minuteField }}" aria-label="{{ __('Menit') }}" class="{{ $inputClass }}">
                    <option value="">{{ __('Menit') }}</option>
                    @foreach ($scheduleOptions['minutes'] as $minute)
                        <option value="{{ $minute }}" @selected((string) old($minuteField, $scheduleValues[$minuteField]) === $minute)>{{ $minute }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="{{ $periodField }}" class="sr-only">{{ __('Periode') }}</label>
                <select id="{{ $periodField }}" name="{{ $periodField }}" aria-label="{{ __('Periode') }}" class="{{ $inputClass }}">
                    <option value="">{{ __('AM/PM') }}</option>
                    @foreach ($scheduleOptions['periods'] as $period)
                        <option value="{{ $period }}" @selected((string) old($periodField, $scheduleValues[$periodField]) === $period)>{{ $period }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @foreach ([$hourField, $minuteField, $periodField] as $field)
            @error($field)<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        @endforeach
    </fieldset>
@endforeach

<div class="md:col-span-2">
    <p class="{{ $helpClass }}">{{ __('Kosongkan semua pilihan jika jadwal belum tersedia.') }}</p>

    @if (! empty($legacySchedule))
        <div class="mt-3 rounded-xl border border-[#D9C79E] bg-[#FFF8E6] p-3 text-sm text-[#735C20] dark:border-[#5A4A26] dark:bg-[#241F14] dark:text-[#E8D49A]">
            <p>{{ __('Jadwal tersimpan sebelumnya: :schedule', ['schedule' => $legacySchedule]) }}</p>
            <p class="mt-1 text-xs">{{ __('Pilih jadwal baru untuk mengganti jadwal lama.') }}</p>
        </div>
    @endif

    @if (! empty($hasSchedule))
        <label class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-[#526071] dark:text-[#AEB8C7]">
            <input type="checkbox" name="clear_schedule" value="1" @checked(old('clear_schedule')) class="rounded border-[#DDD6CC] text-[#B33A3A] focus:ring-[#B33A3A] dark:border-[#2A333D] dark:bg-[#0E1116]">
            {{ __('Hapus jadwal operasional') }}
        </label>
        @error('clear_schedule')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
    @endif
</div>
