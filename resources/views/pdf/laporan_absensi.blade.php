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
        <h1>LAPORAN PRESENSI PERSONEL</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Nama</th>
                <th style="width: 20%">Pangkat / NRP</th>
                <th style="width: 15%">Jabatan</th>
                <th style="width: 10%">Tanggal</th>
                <th style="width: 10%">Jam Masuk</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Ket</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $attendance->user->name ?? '-' }}</td>
                    <td>{{ ($attendance->user->pangkat ?? '-') . ' / ' . ($attendance->user->nrp ?? '-') }}</td>
                    <td>{{ $attendance->user->jabatan ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($attendance->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $attendance->waktu_masuk ?? '-' }}</td>
                    <td class="text-center">{{ $attendance->status }}</td>
                    <td>{{ $attendance->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data presensi untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>
