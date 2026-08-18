<?php

namespace BackupCenter\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class ExportPdfService
{
    public function download(
        string $title,
        array $headers,
        array $rows,
        string $filename
    ): void {

        $options = new Options();

        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = '
        <html>

        <head>

        <style>

        body{

            font-family:Arial;

            font-size:12px;

            color:#333;

        }

        h1{

            margin:0;

            color:#2563EB;

        }

        h2{

            margin-top:5px;

            margin-bottom:20px;

            color:#555;

        }

        table{

            width:100%;

            border-collapse:collapse;

        }

        th{

            background:#2563EB;

            color:white;

            padding:8px;

            border:1px solid #ccc;

        }

        td{

            padding:7px;

            border:1px solid #ddd;

        }

        tr:nth-child(even){

            background:#f8f8f8;

        }

        .footer{

            margin-top:30px;

            font-size:10px;

            color:#777;

            text-align:right;

        }

        </style>

        </head>

        <body>

        <h1>BACKUP CENTER ENTERPRISE</h1>

        <h2>'.$title.'</h2>

        <p><strong>Generado:</strong> '.date('d/m/Y H:i:s').'</p>

        <table>

        <thead>

        <tr>';

        foreach($headers as $header){

            $html .= "<th>{$header}</th>";

        }

        $html .= '

        </tr>

        </thead>

        <tbody>';

        foreach($rows as $row){

            $html .= "<tr>";

            foreach($row as $value){

                $html .= "<td>{$value}</td>";

            }

            $html .= "</tr>";

        }

        $html .= '

        </tbody>

        </table>

        <div class="footer">

            Backup Center Enterprise

        </div>

        </body>

        </html>';

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4','landscape');

        $dompdf->render();

        $dompdf->stream(

            $filename.".pdf",

            [

                "Attachment"=>true

            ]

        );

        exit;

    }

}