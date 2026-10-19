<?php
namespace App\Helpers;
use Carbon\Carbon;
use DateTime;

class DateHelper
{
	public static function getCurrentDate($format="Y-m-d H:i:s", $time_zone=null) {
        if ($time_zone) {
            return Carbon::now($time_zone)->format($format);
        } else {
            return Carbon::now()->format($format);
        }
    }

    public static function addDateTime($unit,$src,$n, $format="") {
        if($src=='now') {
            $s = Carbon::now();
        } else {
            $s = Carbon::parse($src);
        }
        $unit = "add".ucwords(strtolower($unit))."s";
        $ret = $s->$unit($n);
        if(!empty($format)) {
            return $ret->format($format);
        } else {
            return $ret;
        }
    }

    public static function compareDate($src,$dest,$comp='eq') {
        $s = ($src == 'now') ? Carbon::now() : Carbon::parse($src);
        $d = ($dest == 'now') ? Carbon::now() : Carbon::parse($dest);
        return $s->$comp($d);
    }

    public static function getDateTimeDiff($unit,$src,$dest) {
        $s = Carbon::parse($src);
        $d = Carbon::parse($dest);
        $unit = "diffIn".ucwords(strtolower($unit))."s";
        return $s->$unit($d,false);
    }

    public static function getDateTimeRanges($start,$end,$unit='month', $format='Y-m') 
    {
        $res = [];
        $n = self::getDateTimeDiff($unit, $start, $end);
        for ($i=0; $i <= $n; $i++) {
            $res[] = self::parsingDate($start, $format);
            $start = self::addDateTime($unit, $start, 1, $format);
        }

        return $res;
    }

    public static function currentDateTime($user_timezone = 'Asia/Jakarta')
    {
        $date = new \DateTime("now", new \DateTimeZone($user_timezone) );

        $only_date = $date->format('Y-m-d');
        $only_time = $date->format('H:i:s');
        $datetime = $date->format('Y-m-d H:i:s');

        return [
            'date' => $only_date,
            'time' => $only_time,
            'datetime' => $datetime
        ];
    }

    public static function parsingDate($src,$format="Y-m-d H:i:s",$opts=[]) {
        if(isset($opts['month_convertion'])) {
            $src = self::monthConvertion($src,$opts['month_convertion']['langFrom'],$opts['month_convertion']['langTo'],$opts['month_convertion']['type']);
        }

        $res =  Carbon::parse($src)->format($format);
        
        if(isset($opts['res_month_convertion'])) {
            $res = self::monthConvertion($res,$opts['res_month_convertion']['langFrom'],$opts['res_month_convertion']['langTo'],$opts['res_month_convertion']['type']);
        }
        return $res;
    }

    public static function monthConvertion($src,$langFrom='en',$langTo='id',$type='long') {
        $months['id'] = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        $months['en'] = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        $src = ucwords(strtolower($src));
        if (strpos($src, "Nop") !== false) {
            $src = str_replace("Nop", "Nov", $src);
        }
        $agustus = (strpos($src, "g") !== false && strpos($src, "s") !== false  && strpos($src, "t") !== false); 
        $split = explode(' ',$src);
        if (count($split) == 1) {
            $split = explode('-',$src);
        }
        if($agustus) {
            $monthFrom = substr($months[$langFrom][7],0,strlen($split[1]));
            $monthTo = ($type=='short') ? substr($months[$langTo][7],0,3) : $months[$langTo][7];
            $src = str_replace($monthFrom, $monthTo, $src);
        } else {
            foreach ($months[$langFrom] as $key => $value) {
                $monthFrom = substr($value,0,strlen($split[1]));
                $monthTo = ($type=='short') ? substr($months[$langTo][$key],0,3) : $months[$langTo][$key];
                $src = str_replace($monthFrom, $monthTo, $src);
            }
        }
        return $src;
    }

    public static function serializeDateTime($src,$unit="day") {
        return Carbon::parse($src)->$unit;
    }

    public static function convertTimestampToString($src,$format="Y-m-d H:i:s") {
        if(is_numeric($src)) {
            return Carbon::createFromTimestamp($src)->format($format);
        } else {
            return self::parsingDate($src,$format);
        }
    }

    public static function milisecondToDateTime($s) {
        return date("Y-m-d H:i:s",$s/1000);
    }

    public static function createDateTime($y=null,$m=1,$d=1,$h=0,$i=0,$s=0){
        if(is_null($y)) $y = date("Y");
        $dt = Carbon::create($y, $m, $d, $h, $i, $s);
        return $dt;
    }
    
	public static function getLastDayOfMonth($dt,$format="Y-m-d H:i:s"){
        return $dt->endOfMonth()->format($format);
    }

    public static function dateFormat($src, $format='Y-m-d H:i:s') {
        return Carbon::parse($src)->format($format);
    }

    public static function isEndOfYearDate($date)
    {
        return (self::parsingDate($date,'dm')==3112);
    }

    public static function isStartOfYearDate($date)
    {
        return (self::parsingDate($date,'m')==1 && self::parsingDate($date,'d')==1 );
    }

    public static function isEndOfMonth($date)
    {
        return (self::parsingDate($date,'Y-m-d')==self::parsingDate($date,'Y-m-t'));
    }

    public static function getPeriodFromDate($date, $dateStart=null)
    {
        $period = 'daily';

        if (!isset($date)) return $period;

        if (!isset($dateStart)) {
            if (self::isEndOfYearDate($date)) {
                $period = 'yearly';
            } else if (self::isEndOfMonth($date)) {
                $period = 'monthly';
            }
        } else {
            if (self::isStartOfYearDate($dateStart) && self::isEndOfYearDate($date)) {
                $period = 'yearly';
            } else if (self::parsingDate($dateStart,'d')==1 && self::isEndOfMonth($date)) {
                $period = 'monthly';
            }
        }

        return $period;
    }

    public static function addTime($time_a, $time_b, $type = 'plus') 
    {
        if (strlen($time_a) == 8) {
            $time_a = substr($time_a, 0, -3);
        }

        if (strlen($time_b) == 8) {
            $time_b = substr($time_b, 0, -3);
        }

        $carbon_a = Carbon::createFromFormat('H:i', $time_a);
        $carbon_b = Carbon::createFromFormat('H:i', $time_b);

        // Tambahkan waktu
        if ($type == 'plus') {
            $result = $carbon_a->addHours($carbon_b->hour)->addMinutes($carbon_b->minute);
        } else {
            $result = $carbon_a->subHours($carbon_b->hour)->subMinutes($carbon_b->minute);
        }

        // Format kembali ke string dengan format 24 jam
        return $result->format("H:i");
    }

    public static function isDateInRange($selectedDate, $startDate, $endDate) {
        // Parsing tanggal menggunakan DateTime
        $selectedDateTime = new DateTime($selectedDate);
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);

        // Memeriksa apakah tanggal dipilih berada dalam rentang yang ditentukan
        return ($selectedDateTime >= $startDateTime && $selectedDateTime <= $endDateTime);
    }

    public static function countWorkDays($start_date, $end_date, $work_schedules) {
        $total_work_days = 0;
        $current_date = new \DateTime($start_date);
        $end_date = new \DateTime($end_date);

        while ($current_date <= $end_date) {
            // Ambil hari dalam format angka (1 = Senin, 7 = Minggu)
            $day_number = $current_date->format('N'); // ISO-8601 day number (1 = Monday, ..., 7 = Sunday)

            // Cek apakah hari ini ada dalam jadwal kerja dan bukan OFF
            foreach ($work_schedules as $schedule) {
                if ($schedule['day_number'] == $day_number && $schedule['work_shift_code'] !== "OFF") {
                    $total_work_days++;
                    break; // Keluar dari loop karena hari ini sudah terhitung
                }
            }

            // Pindah ke hari berikutnya
            $current_date->modify('+1 day');
        }

        return $total_work_days;
    }

    public static function convertTimeTo(string $source_time, string $unit = 'seconds'): float
    {
        // Pecah durasi menjadi jam, menit, dan detik
        list($hours, $minutes, $seconds) = explode(':', $source_time);

        // Konversi semuanya ke detik
        $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;

        // Hitung berdasarkan unit yang dipilih
        switch ($unit) {
            case 'minutes':
                return $total_seconds / 60; // Konversi ke menit
            case 'hours':
                return $total_seconds / 3600; // Konversi ke jam
            case 'seconds':
            default:
                return $total_seconds; // Tetap dalam detik
        }
    }

    public static function calculateTotalDays($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        return $startDate->diffInDays($endDate) + 1;
    }

    public static function getEffectiveMinutes($start_time, $end_time, $rest_start_time = '00:00:00', $rest_end_time = '00:00:00') {
        // Konversi waktu ke objek DateTime
        $start = new \DateTime($start_time);
        $end = new \DateTime($end_time);
        $rest_start = new \DateTime($rest_start_time);
        $rest_end = new \DateTime($rest_end_time);

        // Hitung total durasi kerja dalam menit
        $total_minutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;

        // Hitung durasi istirahat dalam menit
        $rest_minutes = 0;

        // Jika waktu istirahat berada dalam rentang kerja, hitung durasi istirahat
        if ($rest_start >= $start && $rest_end <= $end) {
            $rest_minutes = ($rest_end->getTimestamp() - $rest_start->getTimestamp()) / 60;
        }

        // Hitung total menit efektif (kerja - istirahat)
        $effective_minutes = $total_minutes - $rest_minutes;

        return $effective_minutes;
    }


// function subtractTime($time1, $time2) {
//     // Buat objek DateTime untuk kedua waktu
//     $datetime1 = new DateTime($time1);
//     $datetime2 = new DateTime($time2);

//     // Hitung selisih waktu
//     $interval = $datetime1->diff($datetime2);

//     // Format hasil selisih waktu
//     return $interval->format('%H:%I:%S');
// }
}
