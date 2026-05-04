import { ShoppingCart, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

export interface POSReceiptItem {
    id: string | number;
    name: string;
    quantity: number;
    unitPrice: number;
}

interface POSReceiptProps {
    branch?: string;
    cashier?: string;
    items: POSReceiptItem[];
    taxRate?: number;
    discountAmount?: number;
    paymentMethod?: string;
    onConfirm?: () => void;
    onRemoveItem?: (id: string | number) => void;
    isLoading?: boolean;
    disabled?: boolean;
}

function formatCurrency(amount: number): string {
    return `₱${amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function POSReceipt({
    branch,
    cashier,
    items,
    taxRate = 0.12,
    discountAmount = 0,
    paymentMethod = 'Cash',
    onConfirm,
    onRemoveItem,
    isLoading = false,
    disabled = false,
}: POSReceiptProps) {
    const subtotal = items.reduce((sum, item) => sum + item.unitPrice * item.quantity, 0);
    const taxAmount = subtotal * taxRate;
    const total = subtotal + taxAmount - discountAmount;

    return (
        <div className="bg-card border rounded-2xl overflow-hidden shadow-xl flex flex-col">
            <div className="bg-primary/5 border-b px-5 py-3.5 flex items-center gap-2">
                <ShoppingCart className="w-4 h-4 text-primary" />
                <span className="text-sm font-semibold">Point of Sale</span>
                {(branch || cashier) && (
                    <span className="ml-auto text-xs text-muted-foreground">
                        {[branch, cashier].filter(Boolean).join(' — ')}
                    </span>
                )}
            </div>

            <div className="flex-1 p-5 space-y-2.5 overflow-y-auto">
                {items.length === 0 ? (
                    <div className="py-10 text-center text-sm text-muted-foreground">
                        No items added yet.
                    </div>
                ) : (
                    items.map((item) => (
                        <div
                            key={item.id}
                            className="flex items-center justify-between rounded-lg bg-muted/40 px-4 py-3 gap-3"
                        >
                            <div className="min-w-0 flex-1">
                                <p className="text-sm font-medium truncate">{item.name}</p>
                                <p className="text-xs text-muted-foreground">
                                    × {item.quantity} @ {formatCurrency(item.unitPrice)}
                                </p>
                            </div>
                            <p className="text-sm font-semibold flex-shrink-0">
                                {formatCurrency(item.unitPrice * item.quantity)}
                            </p>
                            {onRemoveItem && (
                                <button
                                    onClick={() => onRemoveItem(item.id)}
                                    className="text-muted-foreground hover:text-destructive transition-colors flex-shrink-0"
                                    aria-label="Remove item"
                                >
                                    <Trash2 className="w-3.5 h-3.5" />
                                </button>
                            )}
                        </div>
                    ))
                )}
            </div>

            <div className="border-t p-5 space-y-3">
                <div className="space-y-1.5 text-sm">
                    <div className="flex justify-between text-muted-foreground">
                        <span>Subtotal</span>
                        <span>{formatCurrency(subtotal)}</span>
                    </div>
                    {discountAmount > 0 && (
                        <div className="flex justify-between text-emerald-600">
                            <span>Discount</span>
                            <span>-{formatCurrency(discountAmount)}</span>
                        </div>
                    )}
                    <div className="flex justify-between text-muted-foreground">
                        <span>Tax ({(taxRate * 100).toFixed(0)}%)</span>
                        <span>{formatCurrency(taxAmount)}</span>
                    </div>
                    <div className="flex justify-between font-bold text-base pt-1.5 border-t">
                        <span>Total</span>
                        <span>{formatCurrency(total)}</span>
                    </div>
                </div>

                {onConfirm && (
                    <Button
                        className="w-full"
                        size="lg"
                        onClick={onConfirm}
                        disabled={disabled || isLoading || items.length === 0}
                    >
                        {isLoading ? 'Processing...' : `Confirm Payment — ${paymentMethod}`}
                    </Button>
                )}
            </div>
        </div>
    );
}
