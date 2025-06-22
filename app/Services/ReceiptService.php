<?php
// File: app/Services/ReceiptService.php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class ReceiptService
{
    protected $companyInfo;

    public function __construct()
    {
        $this->companyInfo = [
            'name' => 'Jewelry Store',
            'address' => '123 Gold Street, Jewelry District',
            'city' => 'Sydney, NSW 2000',
            'country' => 'Australia',
            'phone' => '+61 2 1234 5678',
            'email' => 'contact@jewelrystore.com',
            'abn' => 'ABN 12 345 678 901',
            'website' => 'www.jewelrystore.com',
            'logo' => 'images/company-logo.png' // Ensure this exists in public/images/
        ];
    }

    public function generateReceipt(Order $order)
    {
        $receiptData = $this->prepareReceiptData($order);

        $pdf = Pdf::loadView('receipts.template', $receiptData);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Set options
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true
        ]);

        return $pdf;
    }

    public function generateInvoice(Order $order)
    {
        $invoiceData = $this->prepareReceiptData($order);
        $invoiceData['document_type'] = 'INVOICE';
        $invoiceData['document_title'] = 'Tax Invoice';

        $pdf = Pdf::loadView('receipts.invoice', $invoiceData);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    protected function prepareReceiptData(Order $order)
    {
        // Calculate additional details
        $totalWeight = $order->orderItems->sum('weight');
        $averageKarat = $this->calculateAverageKarat($order->orderItems);
        $goldValue = $this->calculateTotalGoldValue($order->orderItems);

        return [
            'order' => $order,
            'company' => $this->companyInfo,
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'billing_address' => $order->billing_address,
                'shipping_address' => $order->shipping_address
            ],
            'order_summary' => [
                'order_number' => $order->order_number,
                'order_date' => $order->created_at,
                'status' => $order->status_label,
                'payment_method' => $order->payment_method,
                'tracking_number' => $order->tracking_number
            ],
            'financial_summary' => [
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'shipping_cost' => $order->shipping_cost,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency
            ],
            'jewelry_summary' => [
                'total_items' => $order->orderItems->count(),
                'total_weight' => $totalWeight,
                'average_karat' => $averageKarat,
                'total_gold_value' => $goldValue,
                'gold_price_at_order' => $order->gold_price_at_order
            ],
            'items' => $order->orderItems,
            'generation_date' => now(),
            'document_type' => 'RECEIPT',
            'document_title' => 'Purchase Receipt'
        ];
    }

    protected function calculateAverageKarat($orderItems)
    {
        if ($orderItems->isEmpty()) {
            return 0;
        }

        $totalWeight = $orderItems->sum('weight');
        $weightedKaratSum = 0;

        foreach ($orderItems as $item) {
            $karatValue = $this->getKaratValue($item->karat);
            $weightedKaratSum += $karatValue * $item->weight;
        }

        $averageKaratValue = $weightedKaratSum / $totalWeight;
        return $this->getKaratFromValue($averageKaratValue);
    }

    protected function calculateTotalGoldValue($orderItems)
    {
        $totalValue = 0;

        foreach ($orderItems as $item) {
            $purity = $this->getKaratPurity($item->karat);
            $goldValue = $item->weight * $item->gold_price * $purity;
            $totalValue += $goldValue;
        }

        return $totalValue;
    }

    protected function getKaratValue($karat)
    {
        return match($karat) {
            '10K' => 10,
            '14K' => 14,
            '18K' => 18,
            '22K' => 22,
            '24K' => 24,
            default => 14
        };
    }

    protected function getKaratFromValue($value)
    {
        if ($value <= 12) return '10K';
        if ($value <= 16) return '14K';
        if ($value <= 20) return '18K';
        if ($value <= 23) return '22K';
        return '24K';
    }

    protected function getKaratPurity($karat)
    {
        return match($karat) {
            '10K' => 0.417,
            '14K' => 0.583,
            '18K' => 0.750,
            '22K' => 0.917,
            '24K' => 1.000,
            default => 0.583
        };
    }

    public function emailReceipt(Order $order, $emailAddress = null)
    {
        $email = $emailAddress ?? $order->customer_email;
        $pdf = $this->generateReceipt($order);

        // Here you would implement email sending
        // Example using Laravel's Mail facade:
        /*
        Mail::to($email)->send(new ReceiptMail($order, $pdf));
        */

        return true;
    }

    public function generateReceiptNumber(Order $order)
    {
        return 'REC-' . $order->order_number . '-' . now()->format('Ymd');
    }

    public function getReceiptPath(Order $order)
    {
        $filename = 'receipt-' . $order->order_number . '.pdf';
        return storage_path('app/receipts/' . $filename);
    }

    public function saveReceiptToDisk(Order $order)
    {
        $pdf = $this->generateReceipt($order);
        $path = $this->getReceiptPath($order);

        // Ensure directory exists
        $directory = dirname($path);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $pdf->output());

        return $path;
    }

    public function getCompanyInfo()
    {
        return $this->companyInfo;
    }

    public function updateCompanyInfo($info)
    {
        $this->companyInfo = array_merge($this->companyInfo, $info);
    }
}
