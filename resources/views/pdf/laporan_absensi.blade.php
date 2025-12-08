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
            @php
                $presensiDetailGroup = $group->filter(function($item) {
                     return stripos($item->status, 'Izin') === false && stripos($item->status, 'Tidak Hadir') === false && stripos($item->status, 'Alpha') === false;
                });
                
                $absenGroup = $group->filter(function($item) {
                    return stripos($item->status, 'Tidak Hadir') !== false || stripos($item->status, 'Alpha') !== false;
                });
                
                $izinGroup = $group->filter(function($item) {
                    return stripos($item->status, 'Izin') !== false;
                });
            @endphp

            {{-- 1. Table PRESENSI --}}
            @if($presensiDetailGroup->isNotEmpty())
            <div style="margin-bottom: 10px; page-break-inside: avoid;">
                <table style="margin-bottom: 5px;">
                    <thead>
                        <tr>
                            <th colspan="7" style="background-color: #ddd; text-align: left; padding-left: 10px;">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} | Presensi
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
                        @foreach($presensiDetailGroup as $attendance)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $attendance->user->name ?? '-' }}</td>
                                <td>{{ ($attendance->user->pangkat ?? '-') . ' / ' . ($attendance->user->nrp ?? '-') }}</td>
                                <td>{{ $attendance->user->jabatan ?? '-' }}</td>
                                <td>{{ ($attendance->waktu_masuk && $attendance->waktu_masuk !== '-') ? \Carbon\Carbon::parse($attendance->waktu_masuk)->format('H:i:s') : '-' }}</td>
                                <td class="text-center">{{ $attendance->status }}</td>
                                <td>{{ $attendance->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- 2. Table ABSEN --}}
            @if($absenGroup->isNotEmpty())
            <div style="margin-bottom: 10px; page-break-inside: avoid;">
                <table style="margin-bottom: 5px;">
                    <thead>
                        <tr>
                            <th colspan="7" style="background-color: #ffebee; text-align: left; padding-left: 10px;">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} | Absen
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
                        @foreach($absenGroup as $attendance)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $attendance->user->name ?? '-' }}</td>
                                <td>{{ ($attendance->user->pangkat ?? '-') . ' / ' . ($attendance->user->nrp ?? '-') }}</td>
                                <td>{{ $attendance->user->jabatan ?? '-' }}</td>
                                <td>-</td>
                                <td class="text-center">{{ $attendance->status }}</td>
                                <td>{{ $attendance->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- 3. Table IZIN --}}
            @if($izinGroup->isNotEmpty())
            <div style="margin-bottom: 10px; page-break-inside: avoid;">
                <table style="margin-bottom: 5px;">
                    <thead>
                        <tr>
                            <th colspan="7" style="background-color: #e3f2fd; text-align: left; padding-left: 10px;">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} | Izin
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 20%">Nama</th>
                            <th style="width: 15%">Pangkat / NRP</th>
                            <th style="width: 15%">Jabatan</th>
                            <th style="width: 15%">Tanggal Izin</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 20%">Ket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($izinGroup as $attendance)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $attendance->user->name ?? '-' }}</td>
                                <td>{{ ($attendance->user->pangkat ?? '-') . ' / ' . ($attendance->user->nrp ?? '-') }}</td>
                                <td>{{ $attendance->user->jabatan ?? '-' }}</td>
                                <td style="font-size: 10px;">
                                    @if(isset($attendance->tanggal_mulai) && isset($attendance->tanggal_selesai))
                                        {{ \Carbon\Carbon::parse($attendance->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($attendance->tanggal_selesai)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{{ $attendance->status }}</td>
                                <td>{{ $attendance->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endforeach
    @endif
</body>
</html>
