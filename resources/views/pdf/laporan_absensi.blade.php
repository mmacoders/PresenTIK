<!DOCTYPE html>
<html>
<head>
    <title>Laporan Presensi Personel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin-bottom: 10px; font-size: 14px;  text-decoration: underline;">KEPOLISIAN NEGARA REPUBLIK INDONESIA DAERAH GORONTALO</h1 >
        <h1 style="margin-top: 0; margin-bottom: 10px; font-size: 14px; text-decoration: underline;">BIDANG TEKNOLOGI INFORMASI DAN KOMUNIKASI</h1>
        
        <h1 style="margin-bottom: 5px;">ABSEN APEL PAGI / SIANG PERSONIL POLRI / PNS</h1>
    </div>

    @if($attendances->isEmpty())
        <table>
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 20%">Nama</th>
                    <th style="width: 15%">Pangkat / NRP</th>
                    <th style="width: 15%">Jabatan</th>
                    <th style="width: 15%">Jam Masuk</th>
                    <th style="width: 10%">Status</th>
                    <th style="width: 20%">Ket</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data presensi untuk periode ini.</td>
                </tr>
            </tbody>
        </table>
    @else
        @foreach($attendances->groupBy('tanggal') as $date => $group)
            <div style="margin-bottom: 10px; page-break-inside: avoid;">
                <table style="margin-bottom: 5px;">
                    <thead>
                        <tr>
                            <th colspan="7" style="background-color: #ddd; text-align: left; padding-left: 10px;">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 20%">Nama</th>
                            <th style="width: 15%">Pangkat / NRP</th>
                            <th style="width: 15%">Jabatan</th>
                            <th style="width: 15%">Jam Masuk</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 20%">Ket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group as $index => $attendance)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $attendance->user->name ?? '-' }}</td>
                                <td>{{ ($attendance->user->pangkat ?? '-') . ' / ' . ($attendance->user->nrp ?? '-') }}</td>
                                <td>{{ $attendance->user->jabatan ?? '-' }}</td>
                                <td>{{ $attendance->waktu_masuk ?? '-' }}</td>
                                <td class="text-center">{{ $attendance->status }}</td>
                                <td>{{ $attendance->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</body>
</html>
