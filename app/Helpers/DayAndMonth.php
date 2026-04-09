<?php

// Function fot day and month on event card


function dayandmonth($date)
    {
        $dateParts = explode('-', $date);
        if (count($dateParts) === 3) {
            $day = $dateParts[2];
            $month = $dateParts[1];

            $monthNamesNepali = [
                '01' => 'Baisakh',
                '02' => 'Jestha',
                '03' => 'Ashad',
                '04' => 'Shrawan',
                '05' => 'Bhadra',
                '06' => 'Ashwin',
                '07' => 'Kartik',
                '08' => 'Mangsir',
                '09' => 'Poush',
                '10' => 'Magh',
                '11' => 'Falgun',
                '12' => 'Chaitra'
            ];

            return [
                'day' => $day,
                'monthNepali' => $monthNamesNepali[$month]
            ];
        }
        return null;
    }

