<?php
// GET (AJAX) - sumber data Select2 barang
public function options(Request $request)
{
    $items = Inventory::when($request->q, function ($q, $s) {
            $q->where(fn ($w) => $w->where('name', 'ilike', "%{$s}%")
                                   ->orWhere('code', 'ilike', "%{$s}%"));
        })
        ->orderBy('name')
        ->limit(30)
        ->get();

    return response()->json([
        'results' => $items->map(fn ($i) => [
            'id'    => $i->id,
            'text'  => "{$i->code} - {$i->name} (stok: {$i->stock})",
            'price' => (float) $i->price,
            'stock' => $i->stock,
        ]),
    ]);
}