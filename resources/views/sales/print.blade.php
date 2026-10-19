<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice - {{ $sale->number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #222; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; background: #fff; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: none; padding: 0; }
        }
    </style>
</head>
<body class="bg-light py-4">

    <div class="container mb-3 no-print text-center">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
            Print Sales Invoice
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-4 ms-2">
            Close
        </button>
    </div>

    <div class="invoice-box shadow-sm">
        <div class="row align-items-center mb-4">
            <div class="col-6">
                <h3 class="fw-bold text-primary mb-0">SALES & PURCHASES</h3>
                <small class="text-muted">Retail & Distribution Division</small>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold mb-0">SALES INVOICE</h4>
                <span class="badge bg-light text-dark border fs-6">{{ $sale->number }}</span>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
            <div class="col-6">
                <strong>Invoice Date:</strong> {{ date('d F Y', strtotime($sale->date)) }}<br>
                <strong>Issued At:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="col-6 text-end">
                <strong>Cashier / Sales:</strong> {{ $user ? $user->name : '-' }}<br>
                <strong>Role:</strong> {{ $user ? $user->role : '-' }}
            </div>
        </div>

        @php
            $grand_total = 0;
        @endphp

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 15%;">Code</th>
                    <th>Item Name</th>
                    <th style="width: 18%;" class="text-end">Unit Price</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 20%;" class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $index => $detail)
                    @php
                        $inventory = $detail->getInventory();
                        $subtotal = (int) $detail->qty * (float) $detail->price;
                        $grand_total += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $inventory ? $inventory->code : '-' }}</td>
                        <td>{{ $inventory ? $inventory->name : '-' }}</td>
                        <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $detail->qty }}</td>
                        <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="5" class="text-end fs-6">GRAND TOTAL:</td>
                    <td class="text-end fs-5 text-primary">Rp {{ number_format($grand_total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-5 pt-4">
            <div class="col-6 text-center">
                <p class="mb-5">Customer Acceptance,</p>
                <p class="fw-bold">( .................................... )</p>
            </div>
            <div class="col-6 text-center">
                <p class="mb-5">Authorized Cashier,</p>
                <p class="fw-bold">( {{ $user ? $user->name : 'Cashier' }} )</p>
            </div>
        </div>
    </div>

</body>
</html>
