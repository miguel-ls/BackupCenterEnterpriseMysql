<?php

namespace BackupCenter\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExportExcelService
{
    public function download(
        string $title,
        array $headers,
        array $rows,
        string $filename
    ): void {

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($title);

        /*
        |--------------------------------------------------------------------------
        | Encabezado
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:D1');

        $sheet->setCellValue(

            'A1',

            'BACKUP CENTER ENTERPRISE'

        );

        $sheet->mergeCells('A2:D2');

        $sheet->setCellValue(

            'A2',

            $title

        );

        $sheet->setCellValue(

            'A4',

            'Generado:'

        );

        $sheet->setCellValue(

            'B4',

            date('d/m/Y H:i:s')

        );

        /*
        |--------------------------------------------------------------------------
        | Cabecera
        |--------------------------------------------------------------------------
        */

        $headerRow = 6;

        foreach ($headers as $index => $header) {

            $column = Coordinate::stringFromColumnIndex($index + 1);

            $sheet->setCellValue(

                $column . $headerRow,

                $header

            );

        }

        /*
        |--------------------------------------------------------------------------
        | Datos
        |--------------------------------------------------------------------------
        */

        $rowNumber = 7;

        foreach ($rows as $row) {

            foreach (array_values($row) as $index => $value) {

                $column = Coordinate::stringFromColumnIndex($index + 1);

                $sheet->setCellValue(

                    $column . $rowNumber,

                    $value

                );

            }

            $rowNumber++;

        }

        /*
        |--------------------------------------------------------------------------
        | Estilos Título
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A1')->getFont()

            ->setBold(true)

            ->setSize(18);

        $sheet->getStyle('A2')->getFont()

            ->setBold(true)

            ->setSize(14);

        /*
        |--------------------------------------------------------------------------
        | Estilo Cabecera
        |--------------------------------------------------------------------------
        */

        $lastColumn = Coordinate::stringFromColumnIndex(

            count($headers)

        );

        $sheet->getStyle(

            "A{$headerRow}:{$lastColumn}{$headerRow}"

        )->applyFromArray([

            'font'=>[

                'bold'=>true,

                'color'=>[

                    'rgb'=>'FFFFFF'

                ]

            ],

            'fill'=>[

                'fillType'=>Fill::FILL_SOLID,

                'startColor'=>[

                    'rgb'=>'2563EB'

                ]

            ],

            'alignment'=>[

                'horizontal'=>Alignment::HORIZONTAL_CENTER

            ]

        ]);

        /*
        |--------------------------------------------------------------------------
        | Bordes
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle(

            "A{$headerRow}:{$lastColumn}".($rowNumber-1)

        )->getBorders()->getAllBorders()

            ->setBorderStyle(

                Border::BORDER_THIN

            );

        /*
        |--------------------------------------------------------------------------
        | Autofiltro
        |--------------------------------------------------------------------------
        */

        $sheet->setAutoFilter(

            "A{$headerRow}:{$lastColumn}{$headerRow}"

        );

        /*
        |--------------------------------------------------------------------------
        | Congelar Cabecera
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane("A7");

        /*
        |--------------------------------------------------------------------------
        | Ajuste automático
        |--------------------------------------------------------------------------
        */

        foreach (

            range(

                1,

                count($headers)

            ) as $i

        ) {

            $sheet

                ->getColumnDimension(

                    Coordinate::stringFromColumnIndex($i)

                )

                ->setAutoSize(true);

        }

        /*
        |--------------------------------------------------------------------------
        | Descargar
        |--------------------------------------------------------------------------
        */

        header(

            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'

        );

        header(

            'Content-Disposition: attachment; filename="'.$filename.'.xlsx"'

        );

        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);

        $writer->save('php://output');

        exit;

    }

}