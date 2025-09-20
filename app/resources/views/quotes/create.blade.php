@extends('layouts.app')

@section('title', 'Create Quote')

@section('content')
<div class="max-w-7xl mx-auto" 
     x-data="quoteForm({ 
        items: {{ $items->toJson() }}, 
        old: {{ json_encode(old('quote_items', [['item_id' => '', 'quantity' => 1, 'unit_price' => 0]])) }},
        currencySymbol: '{{ currency_symbol() }}'
     })">
    <form action="{{ route('quotes.store') }}" method="POST">
        {{-- ... form content ... --}}
        {{-- The form content remains the same, only the x-data and script below change --}}
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-macgray-800">Create Quote</h1>
            <div class="flex space-x-2">
                 <a href="{{ route('quotes.index') }}" class="px-5 py-2 text-sm font-medium text-macgray-700 rounded-md hover:bg-macgray-100">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-macblue-600 text-white rounded-md hover:bg-macblue-700 transition-colors">
                    Save Quote
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-macgray-200">
            <div class="p-6">
                {{-- Header --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-macgray-700">Customer</label>
                        <select name="customer_id" id="customer_id" required class="mt-1 block w-full rounded-md border-macgray-300 shadow-sm sm:text-sm">
                            <option value="">Select a customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="quote_date" class="block text-sm font-medium text-macgray-700">Quote Date</label>
                        <input type="date" name="quote_date" id="quote_date" value="{{ old('quote_date', now()->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-macgray-300 shadow-sm sm:text-sm">
                    </div>
                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-macgray-700">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', now()->addDays(30)->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-macgray-300 shadow-sm sm:text-sm">
                    </div>
                </div>

                {{-- Line Items Table --}}
                <div class="mt-8">
                    <table class="min-w-full">
                        <thead class="border-b border-macgray-300">
                            <tr>
                                <th class="py-2 text-left text-sm font-semibold text-macgray-800 w-1/2">Item</th>
                                <th class="py-2 text-left text-sm font-semibold text-macgray-800">Quantity</th>
                                <th class="py-2 text-left text-sm font-semibold text-macgray-800">Price</th>
                                <th class="py-2 text-left text-sm font-semibold text-macgray-800">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="index">
                                <tr>
                                    <td class="pt-4 pr-2">
                                        <select :name="`quote_items[${index}][item_id]`" x-model="line.item_id" @change="updateLine(index)" class="w-full rounded-md border-macgray-300 shadow-sm sm:text-sm">
                                            <option value="">Select an item</option>
                                            <template x-for="item in items">
                                                <option :value="item.id" x-text="item.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="pt-4 pr-2">
                                        <input type="number" :name="`quote_items[${index}][quantity]`" x-model.number="line.quantity" min="1" class="w-full rounded-md border-macgray-300 shadow-sm sm:text-sm">
                                    </td>
                                    <td class="pt-4 pr-2">
                                        <input type="number" :name="`quote_items[${index}][unit_price]`" x-model.number="line.unit_price" step="0.01" min="0" class="w-full rounded-md border-macgray-300 shadow-sm sm:text-sm">
                                    </td>
                                    <td class="pt-4 pr-2">
                                        <span class="py-2" x-text="formatCurrency(line.quantity * line.unit_price)"></span>
                                    </td>
                                    <td class="pt-4">
                                        <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <button type="button" @click="addLine" class="mt-4 px-3 py-1 bg-macgray-200 text-sm font-medium rounded-md hover:bg-macgray-300">
                        + Add Line
                    </button>
                </div>

                {{-- Footer Totals --}}
                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-sm">
                        <div class="flex justify-between py-2 border-b">
                            <span class="font-semibold text-macgray-800">Subtotal</span>
                            <span x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="font-semibold text-macgray-800">Tax (0%)</span>
                            <span x-text="formatCurrency(tax)"></span>
                        </div>
                        <div class="flex justify-between py-3 font-bold text-lg">
                            <span class="text-macgray-900">Total</span>
                            <span x-text="formatCurrency(total)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function quoteForm(config) {
        return {
            items: config.items,
            lines: config.old,
            currencySymbol: config.currencySymbol,
            addLine() {
                this.lines.push({ item_id: '', quantity: 1, unit_price: 0 });
            },
            removeLine(index) {
                if (this.lines.length > 1) this.lines.splice(index, 1);
            },
            updateLine(index) {
                let selectedItem = this.items.find(item => item.id == this.lines[index].item_id);
                if (selectedItem) this.lines[index].unit_price = parseFloat(selectedItem.sale_price);
            },
            formatCurrency(amount) {
                return this.currencySymbol + amount.toFixed(2);
            },
            get subtotal() {
                return this.lines.reduce((acc, line) => acc + (line.quantity * line.unit_price), 0);
            },
            get tax() {
                return this.subtotal * 0; // 0% tax for now
            },
            get total() {
                return this.subtotal + this.tax;
            }
        }
    }
</script>
<script src="//unpkg.com/alpinejs" defer></script>
@endsection