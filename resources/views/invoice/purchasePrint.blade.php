<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice #{{ $invoice->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #000;
            font-size: 12px;
        }

        .invoice-container {
            width: 100%;
            max-width: 90%; /* Standard Thermal Paper Width */
            margin: 0 auto;
            background: #fff;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        @media print {
            body {
                background: none;
            }
            .invoice-container {
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .uppercase { text-transform: uppercase; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .border-b { border-bottom: 1px dashed #000; }
        .border-t { border-top: 1px dashed #000; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }

        .header img {
            max-width: 80px;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .contact-info {
            font-size: 10px;
        }

        .invoice-details {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .customer-info {
            margin-top: 5px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        
        th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            font-size: 11px;
        }
        
        td {
            padding: 3px 0;
            vertical-align: top;
        }

        .totals-table td {
            padding: 2px 0;
        }

        .amount-in-words {
            margin-top: 10px;
            font-style: italic;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
        }
        
        .return-badge {
            border: 1px solid #000;
            padding: 2px 5px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body onload="window.print()">

<div class="invoice-container">
    <!-- Header -->
    <div class="header text-center">
        @if($company && $company->logo_path)
            <img src="{{ asset(str_starts_with($company->logo_path, 'storage/') ? $company->logo_path : 'storage/' . $company->logo_path) }}" alt="Logo">
        @endif
        <div class="company-name">{{ $company->business_name ?? '' }}</div>
        <div class="contact-info">
            {{ $company->phone }} <br> {{ $company->address }}
        </div>
        <div class="font-bold mt-2 uppercase">Purchase Invoice</div>
    </div>

    <!-- Return Status -->
    @if($invoice->inv_status == "Debit_Note_Issued" || $invoice->inv_status == "Returned")
    <div class="text-center mt-2">
        <span class="return-badge">RETURNED / DEBIT NOTE</span>
        @if($invoice->ref)
        <div class="text-xs">Ref: {{ $invoice->ref }}</div>
        @endif
    </div>
    @endif

    <!-- Invoice Details -->
    <div class="invoice-details border-t mt-2 py-1">
        <div class="text-left">
            <div><strong>INV No:</strong> {{ $invoice->id }}</div>
            <div><strong>Date:</strong> {{ date('d-M-Y', strtotime($invoice->day)) }}</div>
        </div>
        <div class="text-right">
            <div><strong>Time:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->timezone('Asia/Karachi')->format('h:i A') }}</div>
            <div><strong>User:</strong> {{ $invoice->user->user ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Vendor -->
    <div class="customer-info">
        <strong>Vendor:</strong> {{ $invoice->account->cust_name ?? 'N/A' }}
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="45%">Item</th>
                <th width="15%" class="text-right">Cost</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="25%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $gross = 0; $count = 0; @endphp
            @foreach($invoice->purchases as $index => $item)
            @php 
                $gross += $item->final; 
                $count++;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'Unknown' }}</td>
                <td class="text-right">{{ number_format($item->price, 0) }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->final, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-t mt-2"></div>

    <!-- Totals -->
    <table class="totals-table">
        <tr>
            <td class="text-left">Total Items: {{ $count }}</td>
            <td class="text-right font-bold">Gross Total:</td>
            <td class="text-right font-bold" width="25%">{{ number_format($gross, 0) }}</td>
        </tr>
        @if($invoice->InvDiscount > 0)
        <tr>
            <td></td>
            <td class="text-right">Discount:</td>
            <td class="text-right">{{ number_format($invoice->InvDiscount, 0) }}</td>
        </tr>
        @endif
        <tr>
            <td></td>
            <td class="text-right font-bold" style="font-size: 14px;">Net Payable:</td>
            <td class="text-right font-bold" style="font-size: 14px;">{{ number_format($invoice->finalVal, 0) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right">Paid:</td>
            <td class="text-right">{{ number_format($invoice->received, 0) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="text-right">Balance:</td>
            <td class="text-right">{{ number_format($invoice->remaining, 0) }}</td>
        </tr>
    </table>

    <!-- Amount in Words -->
    <div class="amount-in-words text-center">
        <span id="words"></span>
    </div>

    <!-- Footer -->
    <div class="footer border-t py-1">
        <p style="font-size: 8px; color: #666;">Software by Arbaz Ehsan (0313-7747660)</p>
    </div>
</div>

<script>
    var a = ['','One ','Two ','Three ','Four ', 'Five ','Six ','Seven ','Eight ','Nine ','Ten ','Eleven ','Twelve ','Thirteen ','Fourteen ','Fifteen ','Sixteen ','Seventeen ','Eighteen ','Nineteen '];
    var b = ['', '', 'Twenty','Thirty','Forty','Fifty', 'Sixty','Seventy','Eighty','Ninety'];

    function inWords (num) {
        if ((num = num.toString()).length > 9) return 'overflow';
        n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
        if (!n) return; var str = '';
        str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
        str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
        str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
        str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
        str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + '' : '';
        return str;
    }

    var num = "{{ $invoice->finalVal }}";
    document.getElementById('words').innerText = "Amount in Words: " + inWords(num) + " Only";
</script>

</body>
</html>