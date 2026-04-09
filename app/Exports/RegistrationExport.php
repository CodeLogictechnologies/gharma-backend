<?php

namespace App\Exports;

use App\Models\BackPanel\Registration as BackPanelRegistration;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles; // Importing WithStyles concern
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Importing Worksheet class
use Exception;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Add this line


class RegistrationExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $searchParams;

    public function __construct($searchParams = [])
    {
        $this->searchParams = $searchParams;
    }

    public function collection()
    {
        try {
            $query = DB::table('registrations as r')
                ->join('provision as p', 'p.provisionid', '=', 'r.p_province_id')
                ->join('district as d', 'd.districtid', '=', 'r.p_district_id')
                ->join('municipality as m', 'm.municipalityid', '=', 'r.p_municipality_id')
                ->select(
                    'r.full_name',
                    'r.dob',
                    'r.contact',
                    'r.code',
                    'r.facebook_url',
                    'r.email',
                    'r.father_name',
                    'r.grandfather_name',
                    'r.mother_name',
                    'r.grandmother_name',
                    'r.t_country',
                    'r.t_city',
                    'r.t_area',
                    'r.t_postal_code',
                    'r.qualification',
                    'r.institution',
                    'r.education_country',
                    'r.job_post',
                    'r.job_company',
                    'r.job_country',
                    'p.provisionname',
                    'd.districtname',
                    'm.municipalityname'
                )
                ->where('status', 'Y')
                ->get();

            return $query->map(function ($item, $index) {
                return [
                    'serial' => $index + 1,
                    'full_name'            => $item->full_name,
                    'dob'                  => $item->dob,
                    'contact'              => $item->contact,
                    'code'                 => $item->code,
                    'facebook_url'         => $item->facebook_url,
                    'email'                => $item->email,
                    'father_name'          => $item->father_name,
                    'grandfather_name'     => $item->grandfather_name,
                    'mother_name'          => $item->mother_name,
                    'grandmother_name'     => $item->grandmother_name,
                    't_country'            => $item->t_country,
                    't_city'               => $item->t_city,
                    't_area'               => $item->t_area,
                    't_postal_code'       => $item->t_postal_code,
                    'qualification'        => $item->qualification,
                    'institution'          => $item->institution,
                    'education_country'    => $item->education_country,
                    'job_post'             => $item->job_post,
                    'job_company'          => $item->job_company,
                    'job_country'          => $item->job_country,
                    'province'             => $item->provisionname,
                    'district'             => $item->districtname,
                    'municipality'         => $item->municipalityname
                ];
            });
        } catch (Exception $e) {
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'Serial No',
            'Full Name',
            'Date of Birth',
            'Contact',
            'Code',
            'facebook_url',
            'Email',
            'Father Name',
            'Grandfather Name',
            'Mother Name',
            'Grandmother Name',
            'Country',
            'City',
            'Area',
            'Postal Code',
            'Qualification',
            'Institution',
            'Education Country',
            'Job Post',
            'Job Company',
            'Job Country',
            'Province',
            'District',
            'Municipality',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
