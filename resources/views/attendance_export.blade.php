<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: middle;
            text-align: center;
        }

        th {
            font-weight: bold;
        }

        td {
            height: 90px;
        }

        .name {
            text-align: left;
            padding-left: 8px;
        }

        .photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid #000;
        }

        .empty {
            color: #999;
            font-size: 10px;
        }

        .time {
            font-size: 10px;
            margin-top: 2px;
        }
    </style>
</head>
<body>

<div class="title">
    DAFTAR HADIR PEGAWAI<br>
    BULAN NOVEMBER 2025
</div>

<table>
    <thead>
        <tr>
            <th style="width:5%">NO</th>
            <th style="width:30%">NAMA</th>
            <th style="width:15%">TANGGAL</th>
            <th style="width:25%">ABSEN PAGI</th>
            <th style="width:25%">ABSEN SORE</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($attendances as $attendance)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="name">{{ $attendance->user->name }}</td>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') }}</td>

            {{-- ABSEN PAGI --}}
            <td>
                @if ($attendance->check_in_photo_base64)
                    <img src="{{ $attendance->check_in_photo_base64 }}" class="photo">
                @else
                    <span class="empty">TIDAK ADA</span>
                @endif
            </td>

            {{-- ABSEN SORE --}}
            <td>
                @if ($attendance->check_out_photo_base64)
                    <img src="{{ $attendance->check_out_photo_base64 }}" class="photo">
                @else
                    <span class="empty">TIDAK ADA</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
