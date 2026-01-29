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
    text-align: center;
}

td { height: 90px; }

.name { text-align:left; }

.photo {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #000;
}

/* ✅ PRINT SETTING */
@page {
    size: A4 portrait;
    margin: 12mm;
}

@media print {
    .no-print { display:none; }

    tr {
        page-break-inside: avoid;
    }

    table {
        page-break-after: auto;
    }
}
</style>
</head>

<body>

@php
$bulan = [
1=>'JANUARI',2=>'FEBRUARI',3=>'MARET',4=>'APRIL',
5=>'MEI',6=>'JUNI',7=>'JULI',8=>'AGUSTUS',
9=>'SEPTEMBER',10=>'OKTOBER',11=>'NOVEMBER',12=>'DESEMBER'
];
@endphp

<div class="title">
DAFTAR HADIR PEGAWAI<br>
BULAN {{ $bulan[$month] }} {{ $year }}
</div>

<button class="no-print" onclick="window.print()">🖨 Print</button>

<table>
<thead>
<tr>
<th>NO</th>
<th>NAMA</th>
<th>TANGGAL</th>
<th>ABSEN PAGI</th>
<th>ABSEN SORE</th>
</tr>
</thead>

<tbody>
@foreach ($attendances as $a)
<tr>
<td>{{ $loop->iteration }}</td>
<td class="name">{{ $a->user->name }}</td>
<td>{{ \Carbon\Carbon::parse($a->date)->format('d-m-Y') }}</td>

<td>
@if($a->check_in_photo)
<img src="{{ Storage::disk('s3')->url($a->check_in_photo) }}" class="photo">
@endif
</td>

<td>
@if($a->check_out_photo)
<img src="{{ Storage::disk('s3')->url($a->check_out_photo) }}" class="photo">
@endif
</td>

</tr>
@endforeach
</tbody>
</table>

</body>
</html>
