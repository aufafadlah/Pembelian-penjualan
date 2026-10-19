<?php

namespace App\Helpers;
use App\Notifications\TelegramError;
use Illuminate\Support\Facades\Notification;
use DateTime;
use Carbon\Carbon;

class GlobalHelper
{
    public static function findString($needle,$haystack,$i,$word)
    {   // $i should be "" or "i" for case insensitive
        if (strtoupper($word)=="W") {   // if $word is "W" then word search instead of string in string search.
            if (preg_match("/\b{$needle}\b/{$i}", $haystack)) {
                return true;
            }
        } else {
            if(preg_match("/{$needle}/{$i}", $haystack)) {
                return true;
            }
        }
        return false;
        // Put quotes around true and false above to return them as strings instead of as bools/ints.
    }

    public static function dayEngToInd($english) {
        if ($english == 'Monday') {
            $day = 'Senin';
        } else if ($english == 'Tuesday') {
            $day = 'Selasa';
        } else if ($english == 'Wednesday') {
            $day = 'Rabu';
        } else if ($english == 'Thursday') {
            $day = 'Kamis';
        } else if ($english == 'Friday') {
            $day = 'Jum\'at';
        } else if ($english == 'Saturday') {
            $day = 'Sabtu';
        } else if ($english == 'Sunday') {
            $day = 'Minggu';
        } else {
            $day = 'Unknown';
        }

        return $day;
    }

    public static function numberToMonthIndo($number) {
        if ($number == '01' || $number == 1) {
            $month = 'Januari';
        } else if ($number == '02' || $number == 2) {
            $month = 'Februari';
        } else if ($number == '03' || $number == 3) {
            $month = 'Maret';
        } else if ($number == '04' || $number == 4) {
            $month = 'April';
        } else if ($number == '05' || $number == 5) {
            $month = 'Mei';
        } else if ($number == '06' || $number == 6) {
            $month = 'Juni';
        } else if ($number == '07' || $number == 7) {
            $month = 'Juli';
        } else if ($number == '08' || $number == 8) {
            $month = 'Agustus';
        } else if ($number == '09' || $number == 9) {
            $month = 'September';
        } else if ($number == '10' || $number == 10) {
            $month = 'Oktober';
        } else if ($number == '11' || $number == 11) {
            $month = 'November';
        } else if ($number == '12' || $number == 12) {
            $month = 'Desember';
        }

        return $month;
    }

    public static function minutes($time)
    {
        $time = explode(':', $time);
        return ($time[0]*60) + ($time[1]) + ($time[2]/60);
    }

    public static function minuteToHourMinute($minutes) 
    {
        $hours = floor($minutes / 60);
        $min = $minutes - ($hours * 60);

        return $hours." jam, ".$min." menit";
    }

    public static function convertSeparator($number, $separator = ',')
    {
        $number = str_replace($separator, '', $number);

        if ($number > 0) {
            return floatval($number);
        }

        return 0;
    }

    public static function periodDateTime($date, $dateTo = null, $onlyYear = false)
    {
        if ($dateTo) {
            $ages_interval = date_diff(date_create($dateTo), date_create($date));
        } else {
            $ages_interval = date_diff(date_create(), date_create($date));
        }
        if ($onlyYear) {
            $age = $ages_interval->format("%Y");
        } else {
            $age = $ages_interval->format("%Y thn, %M bln, %d hr");
        }

        return $age;
    }

    public static function dateIndo($date) 
    {
        if ($date) {
            $expl_time = explode(' ', $date);

            $fullDate = explode('-', $expl_time[0]);

            $date = $fullDate[2];
            $month = $fullDate[1];
            $year = $fullDate[0];

            return $date.' '.self::numberToMonthIndo($month).' '.$year.' '.(isset($expl_time[1]) ? $expl_time[1] : '');
        }

        return '-';
    }

    public static function findArrayByValue($params, $key, $value)
    {
        $res = false;

        if ($params) {
            foreach ($params as $param) {
                if ($param[$key] == $value) {
                    $res = true;
                    continue;
                }
            }
        }

        return $res;
    }

    public static function camelToSnake($camel)
    {
        $snake = preg_replace('/[A-Z]/', '_$0', $camel);
        $snake = strtolower($snake);
        $snake = ltrim($snake, '_');
        return $snake;
    }

    public static function getClientIP()
    {
        $ip = 'Unknown';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $ip_address = explode(',', $ip);
        return $ip_address[0];
    }

    public static function escapeJsonString($value) 
    {  
        $escapers = ['\n'];
        $replacements = [", "];
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }
    
    public static function pushLog($type,$request,$response,$trace=[]) 
    {
        $isAPILimit = false;
        if(isset($response['error_message']['error'])) {
            $error = $response['error_message']['error'];
            $isAPILimit = isset($error['detail']['api_rate_limit']);
        }

        if($type == 'error') {
            $trace_split = [];

            for ($i=0; $i<4; $i++) {
                if (isset($trace['#0'.$i])) {
                    $trace_split['#0'.$i] = $trace['#0'.$i];
                }
            }

            $encoded = json_encode(utf8ize(array_merge($request, $response, ['trace' => $trace_split])));

            \Log::error($encoded);
            if (!env('APP_DEBUG')) {
                Notification::route('telegram', env('TELEGRAM_LOGGER_CHAT_ID'))->notify(new TelegramError(['data' => $encoded]));
            }
        } else {
            $encoded = json_encode(array_merge($request, $response));
            \Log::info(self::escapeJsonString($encoded));
        }
    }

    public static function randomText( $length = 8, $type = 'alnum' )
    {
        switch ( $type ) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = (string) $type;
                break;
        }


        $crypto_rand_secure = function ( $min, $max ) {
            $range = $max - $min;
            if ( $range < 0 ) return $min; // not so random...
            $log    = log( $range, 2 );
            $bytes  = (int) ( $log / 8 ) + 1; // length in bytes
            $bits   = (int) $log + 1; // length in bits
            $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ( $rnd >= $range );
            return $min + $rnd;
        };

        $token = "";
        $max   = strlen( $pool );
        for ( $i = 0; $i < $length; $i++ ) {
            $token .= $pool[$crypto_rand_secure( 0, $max )];
        }

        return $token;
    }

    public static function slugify($text, string $divider = '_')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return 'n-a';
        }

        return $text;
    }

    public static function convert_to_minutes($time) 
    {
        // Parsing waktu
        $parts = explode(':', $time);

        // Menghitung total menit
        $minutes = $parts[0] * 60 + $parts[1];

        return $minutes;
    }

    public static function convertTimeToDay($time, $workingHours, $isInMinutes = false) 
    {
        if (floatval($workingHours) == 8.5) {
            $workingHours = floatval($workingHours) - 0.5;
        } else if (floatval($workingHours) > 6) {
            $workingHours = floatval($workingHours) - 1;
        }
        if ($isInMinutes) {
            $minutes = $time;
        } else {
            $minutes = self::convert_to_minutes($time);
        }
        $hours = $minutes / 60;
        
        $total = $hours / floatval($workingHours);
        

        if ($total < 0) {
            $total = 0;
        }

        return $total;
    }
    
    public static function convertTime($time) {
        $timeParts = explode(':', $time);
        $hours = $timeParts[0];
        $minutes = $timeParts[1];
        $seconds = $timeParts[2];

        if ($hours >= 24) {
            $hours -= 24;
        }

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    public static function calculatePermitRules($minute, $is_late = false) 
    {
        $totalPermit = 0;

        if ($minute < 15) {
            if (!$is_late) {
                $totalPermit = $minute;
            }
        } else if ($minute >= 15 && $minute <= 45) {
            $totalPermit = 30;
        } else if ($minute >= 46 && $minute <= 60) {
            $totalPermit = 60;
        } else {
            while ($minute > 0) {
                if ($minute < 15) {
                    $minute = 0; // Keluar dari loop
                } else if ($minute >= 15 && $minute <= 45) {
                    $totalPermit += 30;
                    $minute -= 30;
                } else {
                    $totalPermit += 60;
                    $minute -= 60;
                }
            }
        }

        return $totalPermit;
    }

    public static function calculate_payroll_formula($base, $expression, $multiplier = 0) 
    {
        // Bersihkan spasi dari string
        $cleanedExpression = str_replace(' ', '', $expression);
        // Pisahkan string menjadi token
        $cleanedExpression = str_replace('{multiplier}', $multiplier, $cleanedExpression);

        $cleanedExpression = str_replace('{base}', $base, $cleanedExpression);

        preg_match_all('/(?:\d+\.\d*|\d+|\S)/', $cleanedExpression, $tokens);

        // Inisialisasi variabel untuk menyimpan hasil
        $result = 0;
        $operator = '*';
        $currentValue = 0;

        // Iterasi melalui token
        foreach ($tokens[0] as $token) {
            if (is_numeric($token)) {
                if ($currentValue > 0) {
                    if ($operator === '+') {
                        $result = $result + $token;
                    } elseif ($operator === '-') {
                        $result = $result - $token;
                    } elseif ($operator === '*') {
                        $result = $result * $token;
                    } elseif ($operator === '/') {
                        $result = $result / $token;
                    }
                } else {
                    $currentValue = floatval($token);
                    $result = floatval($token);
                }
            } elseif ($token === 'x') {
                // Perkalian
                $operator = '*';
            } elseif ($token === ':') {
                // Pembagian
                $operator = '/';
            } else {
                // Operator atau karakter lainnya
                if ($operator === '+') {
                    $result += $currentValue;
                } elseif ($operator === '-') {
                    $result -= $currentValue;
                } elseif ($operator === '*') {
                    $result *= $currentValue;
                } elseif ($operator === '/') {
                    $result /= $currentValue;
                }

                // Setel operator untuk iterasi berikutnya
                $operator = $token;
            }
        }

        return $result;
    }


    public static function calculateOvertime($overtime, $schedules, $holidays, $effectiveDate, $attendance, $rest_start_time ,$rest_end_time ,$rest_duration) 
    {
        // Convert string time to DateTime objects
        $start = new DateTime(self::roundToInterval($overtime['start_time']));
        $end = new DateTime($overtime['end_time']);
        $rest = new DateTime($overtime['rest_time']);
        $overtime_date = Carbon::parse($overtime['overtime_date']);

        $dayOfWeek = $overtime_date->dayOfWeekIso;

        $diff = $start->diff($end);
        $total_hours = $diff->h + ($diff->i / 60) + ($diff->s / 3600);

        if ($attendance['clock_in'] == $overtime['start_time']) {
            if ($overtime['end_time'] >= $rest_end_time) {
                $total_hours = $total_hours - self::convertTimeToDecimal($rest_duration);
            }
        } else {
            if ($total_hours >= 3.5 && $attendance['clock_out'] >= $rest_start_time) {
                $total_hours = $total_hours - 0.5;
            }
        }

        // if ($attendance['employee_code'] == '14042166') {
        //     dd($overtime->toArray());
        // }

        // Deduct rest time from total hours
        
        // $total_hours -= ($rest->format('i') / 60) + ($rest->format('s') / 3600);

        // Initialize overtime parameters
        $addition_count_overtime_1_in_hours = 0;
        $addition_count_overtime_2_in_hours = 0;
        $addition_count_overtime_weekend_in_hours = 0;
        $addition_count_overtime_ph_in_hours = 0;
        $addition_count_overtime_weekend_in_days = 0;

        $is_scheduled_work = self::isScheduledWork($effectiveDate, $schedules, $overtime['overtime_date']);
        // if ($attendance['employee_code'] == '14042166') {
        //     dd($overtime['overtime_date'], $effectiveDate, $schedules, $overtime['overtime_date']);
        // }

        // Check if overtime is more than 1 hour
        if (isset($holidays[$overtime['overtime_date']])) {
            $addition_count_overtime_ph_in_hours = $total_hours;
        } else if (!$is_scheduled_work) {
            $addition_count_overtime_weekend_in_hours = $total_hours;
            $addition_count_overtime_weekend_in_days++;
        } else {
            if ($total_hours > 1) {
                // Calculate the first hour of overtime
                $addition_count_overtime_1_in_hours = 1;

                // Calculate the remaining overtime (excluding the first hour)
                $remaining_overtime = self::customRoundOvertime($total_hours - 1);

                // Check if there's more than 1 hour of remaining overtime
                if ($remaining_overtime > 0) {
                    $addition_count_overtime_2_in_hours = $remaining_overtime;
                }
            } else if ($total_hours > 0) {
                // Less than 1 hour of overtime
                $addition_count_overtime_1_in_hours = $total_hours;
            }
        }

        // Return the result
        return [
            'addition_count_overtime_1_in_hours' => self::customRoundOvertime($addition_count_overtime_1_in_hours),
            'addition_count_overtime_2_in_hours' => self::customRoundOvertime($addition_count_overtime_2_in_hours),
            'addition_count_overtime_weekend_in_hours' => self::customRoundOvertime($addition_count_overtime_weekend_in_hours),
            'addition_count_overtime_ph_in_hours' => self::customRoundOvertime($addition_count_overtime_ph_in_hours),
            'addition_count_overtime_weekend_in_days' => $addition_count_overtime_weekend_in_days
        ];
    }

    public static function customRoundOvertime($number)
    {
        // Jika nilai desimal lebih besar atau sama dengan 0.5, bulatkan ke 0.5
        if ($number - floor($number) >= 0.5) {
            return floor($number) + 0.5;
        }
        // Jika nilai desimal kurang dari 0.5, bulatkan ke bawah
        else {
            return floor($number);
        }
    }

    public static function isScheduledWork($effectiveDate, $patterns, $targetDate)
    {
        // Menghitung selisih hari antara effective date dan target date
        $daysDiff = Carbon::parse($effectiveDate)->diffInDays(Carbon::parse($targetDate));

        // Menghitung pattern number berdasarkan selisih hari
        $patternNumber = $daysDiff % count($patterns) + 1;

        // Mendapatkan status kerja atau off berdasarkan pattern number
        $status = $patterns[$patternNumber];

        return $status == 'work' ? true : false;
    }

    public static function countWorkDaysInRange($effectiveDate, $patterns, $startDate, $endDate, $public_holidays)
    {
        $workDaysCount = 0;

        $currentDate = Carbon::parse($startDate);

        while ($currentDate <= $endDate) {
            $patternNumber = Carbon::parse($effectiveDate)->diffInDays($currentDate) % count($patterns) + 1;
            $status = $patterns[$patternNumber];

            $is_public_holiday = false;

            if (isset($public_holidays[$currentDate->format('Y-m-d')]) && $public_holidays[$currentDate->format('Y-m-d')]) {
                $is_public_holiday = true;
            }

            if ($status === 'work' && !$is_public_holiday) {
                $workDaysCount++;
            }

            $currentDate->addDay();
        }

        return $workDaysCount;
    }

    public static function calculateWorkingDays($startDate, $endDate, $workSchedules, $effectiveDate) 
    {
        //TODO WOI
        $workingDayTotal = 0;
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeekIso;

            if (in_array($dayOfWeek, $workSchedules)) {
                $workingDayTotal++;
            }

            $currentDate->addDay();
        }

        return $workingDayTotal;
    }

    public static function calculateWorkScheduleAndDuration($entry_time, $work_duration) 
    {
        // Konversi string waktu ke dalam format timestamp
        $timestamp_entry_time = strtotime($entry_time);
        
        // Konversi durasi kerja menjadi detik
        $duration_in_seconds = strtotime($work_duration) - strtotime('00:00:00');
        
        // Hitung jam selesai
        $end_date_timestamp = $timestamp_entry_time + $duration_in_seconds;
        
        // Format hasil dalam bentuk string jam:menit
        $end_date = date('H:i', $end_date_timestamp);
        
        return $end_date;
    }

    public static function calculateTimeDiff($scheduledTime, $realTime, $is_reverse = false, $is_carbon = false) 
    {
        // Konversi string waktu ke objek Carbon

        if (!$is_carbon) {
            // $scheduledTimeObj = Carbon::createFromFormat('H:i:s', $scheduledTime);
            $scheduledTimeObj = Carbon::createFromFormat('H:i:s', self::formatTime($scheduledTime));
            // $realTimeObj = Carbon::createFromFormat('H:i:s', $realTime);
            $realTimeObj = Carbon::createFromFormat('H:i:s', self::formatTime($realTime));
        } else {
            $scheduledTimeObj = $scheduledTime;
            $realTimeObj = $realTime;
        }

        if (!$is_reverse) {
            if ($realTimeObj->lessThan($scheduledTimeObj)) {
                return '00:00:00';
            }
        } else {
            if ($realTimeObj->greaterThan($scheduledTimeObj)) {
                return '00:00:00';
            }
        }

        // Hitung diffTime waktu
        $diffTime = $realTimeObj->diff($scheduledTimeObj);

        // Format hasil diffTime waktu
        $results = $diffTime->format('%H:%I:%S');

        return $results;
    }

    public static function formatTime($time) 
    {
        // Pemeriksaan apakah format time sudah termasuk detik
        if (strlen($time) === 5) {
            // Jika format time HH:mm, tambahkan :00 di belakangnya
            $time .= ':00';
        }

        return $time;
    }

    public static function convertTimeToDecimal($timeString)
    {
        list($hour, $minute) = explode(':', $timeString);
        $decimalTime = $hour + ($minute / 60);
        return $decimalTime;
    }

    public static function roundToInterval($time)
    {
        $hour = date('H', strtotime($time));
        $minute = date('i', strtotime($time));

        if ($minute >= 45) {
            // Jika menit lebih besar atau sama dengan 45, bulatkan ke jam berikutnya
            $roundedTime = sprintf('%02d:00', $hour + 1);
        } elseif ($minute >= 15) {
            // Jika menit lebih besar atau sama dengan 15, bulatkan ke setengah jam berikutnya
            $roundedTime = sprintf('%02d:30', $hour);
        } else {
            // Jika menit kurang dari 15, bulatkan ke jam sekarang
            $roundedTime = sprintf('%02d:00', $hour);
        }

        return $roundedTime;
    }

    public static function convertFloatToDaysAndHours($floatValue) 
    {
        // Ambil bagian hari (bilangan bulat)
        $days = floor($floatValue);

        // Ambil bagian jam (bagian desimal)
        $hoursDecimal = $floatValue - $days;

        return [
            'days' => $days,
            'hours' => $hoursDecimal,
        ];
    }

    public static function calculateTerPercentage($data, $terType, $grossIncome) {
        // Temukan array TER berdasarkan ter_type
        $terArray = array_values(array_filter($data, function($item) use ($terType) {
            return $item[0]['ter_type'] === $terType;
        }));

        // Jika TER tidak ditemukan, kembalikan pesan error
        if (empty($terArray)) {
            return 0;
        }

        // Loop melalui setiap elemen TER
        foreach ($terArray[0] as $ter) {
            // Cek jika gross_income berada dalam rentang
            if ($grossIncome >= $ter['gross_income_from'] && $grossIncome <= $ter['gross_income_to']) {
                // Kembalikan persentase yang sesuai
                return $ter['percentage'];
            }
        }

        // Kembalikan pesan error jika tidak ada rentang yang cocok
        return 0;
    }

    public static function csv_to_array($filename, $header)
    {
        $delimiter = ',';
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}