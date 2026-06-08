<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timezones;
use Illuminate\Support\Facades\Schema;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Timezones::truncate();

        if (Schema::hasTable('timezones')) { 
            \DB::statement("INSERT INTO `timezones` (`id`, `value`, `label`, `gmt_value`) VALUES
                (1, '-12.00',   '(GMT -12:00) Eniwetok, Kwajalein','-12:00'),
                (2, '-11.00',   '(GMT -11:00) Midway Island, Samoa','-11:00'),
                (3, '-10.00',   '(GMT -10:00) Hawaii','-10:00'),
                (4, '-09.50',   '(GMT -9:30) Taiohae','-9:30'),
                (5, '-09.00',   '(GMT -9:00) Alaska','-9:00'),
                (6, '-08.00',   '(GMT -8:00) Pacific Time (US &amp; Canada)','-8:00'),
                (7, '-07.00',   '(GMT -7:00) Mountain Time (US &amp; Canada)','-7:00'),
                (8, '-06.00',   '(GMT -6:00) Central Time (US &amp; Canada), Mexico City','-6:00'),
                (9, '-05.00',   '(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima','-5:00'),
                (10,    '-04.50',   '(GMT -4:30) Caracas','-4:30'),
                (11,    '-04.00',   '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz','-4:00'),
                (12,    '-03.50',   '(GMT -3:30) Newfoundland','-3:30'),
                (13,    '-03.00',   '(GMT -3:00) Brazil, Buenos Aires, Georgetown','-3:00'),
                (14,    '-02.00',   '(GMT -2:00) Mid-Atlantic','-2:00'),
                (15,    '-01.00',   '(GMT -1:00) Azores, Cape Verde Islands','-1:00'),
                (16,    '+00.00',   '(GMT) Western Europe Time, London, Lisbon, Casablanca','+00:00'),
                (17,    '+01.00',   '(GMT +1:00) Brussels, Copenhagen, Madrid, Paris','+1:00'),
                (18,    '+02.00',   '(GMT +2:00) Kaliningrad, South Africa','+2:00'),
                (19,    '+03.00',   '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg','+3:00'),
                (20,    '+03.50',   '(GMT +3:30) Tehran','+3:30'),
                (21,    '+04.00',   '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi','+4:00'),
                (22,    '+04.50',   '(GMT +4:30) Kabul','+4:30'),
                (23,    '+05.00',   '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent','+5:00'),
                (24,    '+05.50',   '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi','+5:30'),
                (25,    '+05.75',   '(GMT +5:45) Kathmandu, Pokhar','+5:45'),
                (26,    '+06.00',   '(GMT +6:00) Almaty, Dhaka, Colombo','+6:00'),
                (27,    '+06.50',   '(GMT +6:30) Yangon, Mandalay','+6:30'),
                (28,    '+07.00',   '(GMT +7:00) Bangkok, Hanoi, Jakarta','+7:00'),
                (29,    '+08.00',   '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong','+8:00'),
                (30,    '+08.75',   '(GMT +8:45) Eucla','+8:45'),
                (31,    '+09.00',   '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk','+9:00'),
                (32,    '+09.50',   '(GMT +9:30) Adelaide, Darwin','+9:30'),
                (33,    '+10.00',   '(GMT +10:00) Eastern Australia, Guam, Vladivostok','+10:00'),
                (34,    '+10.50',   '(GMT +10:30) Lord Howe Island','+10:30'),
                (35,    '+11.00',   '(GMT +11:00) Magadan, Solomon Islands, New Caledonia','+11:00'),
                (36,    '+11.50',   '(GMT +11:30) Norfolk Island','+11:30'),
                (37,    '+12.00',   '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka','+12:00'),
                (38,    '+12.75',   '(GMT +12:45) Chatham Islands','+12:45'),
                (39,    '+13.00',   '(GMT +13:00) Apia, Nukualofa','+13:00'),
                (40,    '+14.00',   '(GMT +14:00) Line Islands, Tokelau', '+14:00');");
        }
    }
}
