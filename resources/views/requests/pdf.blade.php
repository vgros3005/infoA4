<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $oRequest->reference }} — Fiche A4 v{{ $iVersion ?? 1 }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #212529;
            line-height: 1.5;
        }
        .page {
            padding: 15mm 15mm 20mm 15mm;
        }

        /* En-tête */
        .header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: table;
            width: 100%;
        }
        .header-left { display: table-cell; vertical-align: middle; width: 60%; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
        .logo-text {
            font-size: 18pt;
            font-weight: bold;
            color: #0d6efd;
            letter-spacing: 1px;
        }
        .logo-sub { font-size: 8pt; color: #6c757d; }
        .ref-badge {
            background-color: #0d6efd;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12pt;
            font-weight: bold;
            display: inline-block;
        }
        .version-info {
            font-size: 8pt;
            color: #6c757d;
            margin-top: 4px;
        }

        /* Titre principal */
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            color: #212529;
            margin-bottom: 8px;
        }
        .badges { margin-bottom: 12px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8pt;
            color: white;
            margin-right: 4px;
        }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-outline { background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6; }

        /* Sections */
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Grille métadonnées */
        .meta-grid { display: table; width: 100%; }
        .meta-row { display: table-row; }
        .meta-label {
            display: table-cell;
            font-weight: bold;
            color: #6c757d;
            width: 140px;
            padding: 2px 8px 2px 0;
            font-size: 9pt;
            vertical-align: top;
        }
        .meta-value {
            display: table-cell;
            padding: 2px 0;
            font-size: 9pt;
            vertical-align: top;
        }

        /* Tableau sociétés/logiciels */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 6px;
        }
        table.data-table th {
            background-color: #e9ecef;
            padding: 4px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        table.data-table td {
            padding: 4px 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td { background-color: #f8f9fa; }

        /* Contenu WYSIWYG */
        .wysiwyg-content {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 10px;
            font-size: 9pt;
            line-height: 1.6;
        }
        .wysiwyg-content img { max-width: 100%; height: auto; }
        .wysiwyg-content table { border-collapse: collapse; width: 100%; }
        .wysiwyg-content table td, .wysiwyg-content table th {
            border: 1px solid #dee2e6;
            padding: 4px 6px;
        }

        /* Historique statuts */
        .history-row { display: table-row; }
        .history-table { display: table; width: 100%; }
        .history-date { display: table-cell; width: 120px; color: #6c757d; font-size: 8.5pt; vertical-align: top; padding: 3px 0; }
        .history-user { display: table-cell; width: 140px; font-weight: bold; font-size: 8.5pt; vertical-align: top; padding: 3px 0; }
        .history-status { display: table-cell; vertical-align: top; padding: 3px 0; font-size: 8.5pt; }
        .history-comment { display: table-cell; color: #6c757d; font-style: italic; font-size: 8.5pt; vertical-align: top; padding: 3px 0 3px 8px; }

        /* Pied de page */
        .footer {
            position: fixed;
            bottom: 8mm;
            left: 15mm;
            right: 15mm;
            border-top: 1px solid #dee2e6;
            padding-top: 4px;
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; font-size: 7.5pt; color: #6c757d; }
        .footer-right { display: table-cell; text-align: right; font-size: 7.5pt; color: #6c757d; }
        .frozen-stamp {
            color: #dc3545;
            font-weight: bold;
            border: 1px solid #dc3545;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 7pt;
            text-transform: uppercase;
        }

        /* Page break utility */
        .page-break { page-break-after: always; }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
<div class="page">

    {{-- Pied de page fixe --}}
    <div class="footer">
        <div class="footer-left">
            {{ config('app.name') }} — {{ $oRequest->reference }} — v{{ $iVersion ?? 1 }} — {{ now()->format('d/m/Y H:i') }}
        </div>
        <div class="footer-right">
            <span class="frozen-stamp">Document figé</span>
        </div>
    </div>

    {{-- En-tête --}}
    <div class="header">
        <div class="header-left">
            <div class="logo-text">FICHES A4</div>
            <div class="logo-sub">Gestion des demandes de développement — DSI</div>
        </div>
        <div class="header-right">
            <div class="ref-badge">{{ $oRequest->reference }}</div>
            <div class="version-info">
                Version {{ $iVersion ?? 1 }} — {{ now()->format('d/m/Y') }}<br>
                Statut : <strong>{{ $oRequest->status->name ?? '—' }}</strong>
            </div>
        </div>
    </div>

    {{-- Titre et badges --}}
    <div class="section">
        <div class="doc-title">{{ $oRequest->title }}</div>
        <div class="badges">
            @if($oRequest->status)
                <span class="badge" style="background-color: {{ $oRequest->status->color ?? '#6c757d' }};">
                    {{ $oRequest->status->name }}
                </span>
            @endif
            @if($oRequest->priority)
                <span class="badge" style="background-color: {{ $oRequest->priority->color ?? '#adb5bd' }};">
                    {{ $oRequest->priority->name }}
                </span>
            @endif
            @if($oRequest->requestType)
                <span class="badge badge-outline">{{ $oRequest->requestType->name }}</span>
            @endif
        </div>
    </div>

    {{-- Métadonnées --}}
    <div class="section no-break">
        <div class="section-title">Informations générales</div>
        <div class="meta-grid">
            <div class="meta-row">
                <div class="meta-label">Demandeur</div>
                <div class="meta-value">{{ $oRequest->requester->full_name ?? '—' }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Date de demande</div>
                <div class="meta-value">{{ $oRequest->requested_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-label">Date souhaitée</div>
                <div class="meta-value">{{ $oRequest->desired_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            @if($oRequest->priority_justification)
            <div class="meta-row">
                <div class="meta-label">Justification priorité</div>
                <div class="meta-value">{{ $oRequest->priority_justification }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Description --}}
    <div class="section no-break">
        <div class="section-title">Description</div>
        <p>{{ $oRequest->description }}</p>
    </div>

    {{-- Contenu WYSIWYG --}}
    @if($oRequest->content)
    <div class="section">
        <div class="section-title">Contenu détaillé</div>
        <div class="wysiwyg-content">
            {!! $oRequest->content !!}
        </div>
    </div>
    @endif

    {{-- Sociétés et logiciels --}}
    @if($oRequest->companies->isNotEmpty() || $oRequest->softwares->isNotEmpty())
    <div class="section no-break">
        <div class="section-title">Périmètre</div>
        <table class="data-table">
            <tr>
                <th style="width: 50%;">Société(s) concernée(s)</th>
                <th style="width: 50%;">Logiciel(s) concerné(s)</th>
            </tr>
            <tr>
                <td>
                    @foreach($oRequest->companies as $oCompany)
                        {{ $oCompany->name }}@if(!$loop->last), @endif
                    @endforeach
                    @if($oRequest->companies->isEmpty())—@endif
                </td>
                <td>
                    @foreach($oRequest->softwares as $oSoftware)
                        {{ $oSoftware->name }}@if(!$loop->last), @endif
                    @endforeach
                    @if($oRequest->softwares->isEmpty())—@endif
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Historique des statuts --}}
    @if(!empty($aStatusHistory) && count($aStatusHistory) > 0)
    <div class="section no-break">
        <div class="section-title">Historique des statuts</div>
        <table class="data-table">
            <tr>
                <th style="width: 120px;">Date</th>
                <th style="width: 140px;">Utilisateur</th>
                <th style="width: 150px;">Statut</th>
                <th>Commentaire</th>
            </tr>
            @foreach($aStatusHistory as $oHistory)
            <tr>
                <td>{{ $oHistory->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $oHistory->user->full_name ?? '—' }}</td>
                <td>
                    @if($oHistory->oldStatus)
                        {{ $oHistory->oldStatus->name }} →
                    @endif
                    <strong>{{ $oHistory->newStatus->name ?? '—' }}</strong>
                </td>
                <td>{{ $oHistory->comment ?? '—' }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

</div>
</body>
</html>
