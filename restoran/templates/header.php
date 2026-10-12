<?php
/**
 * Template Bagian Header HTML
 * Library: Bootstrap 5 CDN & Font Awesome
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dapoer Dimas - Sistem Pemesanan Restoran</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Google Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        :root {
            --brand-primary: #d97706;
            --brand-primary-hover: #b45309;
            --brand-dark: #0f172a;
            --surface-bg: #f8fafc;
            --card-radius: 12px;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--surface-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            letter-spacing: -0.01em;
        }
        .navbar-custom {
            background-color: var(--brand-dark);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.15);
        }
        .navbar-brand {
            font-weight: 700;
            color: #ffffff !important;
            letter-spacing: -0.02em;
        }
        .nav-link {
            color: #cbd5e1 !important;
            font-weight: 500;
            font-size: 0.9375rem;
            padding: 0.5rem 0.85rem !important;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
        }
        .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .nav-link.active {
            color: #ffffff !important;
            background-color: var(--brand-primary) !important;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.35);
        }
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card-header-custom {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-top-left-radius: var(--card-radius) !important;
            border-top-right-radius: var(--card-radius) !important;
        }
        .kpi-card {
            border-radius: var(--card-radius);
            padding: 1.25rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
        }
        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-custom td {
            vertical-align: middle;
            padding: 0.85rem 1rem;
            font-size: 0.9375rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .btn-warning-custom {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.15s ease;
        }
        .btn-warning-custom:hover {
            background-color: var(--brand-primary-hover);
            border-color: var(--brand-primary-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
        }

        /* Modern Soft Badges */
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .badge-soft-success {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0;
        }
        .badge-soft-warning {
            background-color: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a;
        }
        .badge-soft-primary {
            background-color: #e0e7ff !important;
            color: #3730a3 !important;
            border: 1px solid #c7d2fe;
        }
        .badge-soft-danger {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca;
        }
        .badge-soft-secondary {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0;
        }

        /* Modern POS Ordering Styles */
        .segmented-nav {
            background-color: #f1f5f9;
            border-radius: 10px;
            padding: 4px;
            display: inline-flex;
            gap: 4px;
            width: 100%;
        }
        .segmented-btn {
            flex: 1;
            padding: 8px 14px;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 0.875rem;
            color: #64748b;
            border-radius: 8px;
            transition: all 0.15s ease;
            text-align: center;
            cursor: pointer;
        }
        .segmented-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .quick-chip {
            display: inline-block;
            background-color: #f1f5f9;
            color: #475569;
            font-size: 0.8125rem;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.12s ease;
            user-select: none;
        }
        .quick-chip:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }
        .pos-menu-item {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            transition: all 0.15s ease;
            background: #ffffff;
        }
        .pos-menu-item:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .pos-menu-item.active-item {
            border-color: #f59e0b;
            background-color: #fffbeb;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
        }
        .qty-control-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.12s ease;
        }
        .qty-control-btn:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        .qty-control-btn.btn-plus {
            background: #d97706;
            color: #ffffff;
            border-color: #d97706;
        }
        .qty-control-btn.btn-plus:hover {
            background: #b45309;
            border-color: #b45309;
        }
        @media print {
            @page {
                size: auto;
                margin: 8mm 10mm;
            }
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11pt;
            }
            .card-custom {
                border: none !important;
                box-shadow: none !important;
            }
            .nota-struk-box {
                max-width: 480px !important;
                margin: 0 auto !important;
                padding: 16px !important;
                border: 1px dashed #333 !important;
                border-radius: 6px !important;
                background: #ffffff !important;
            }
            .nota-struk-box table {
                border-color: #555 !important;
            }
            .nota-struk-box th, .nota-struk-box td {
                padding: 4px 8px !important;
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>
