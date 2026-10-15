<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sujet }}</title>
</head>
<body style="margin:0; padding:24px 0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                       style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#047857; color:#ffffff; padding:18px 24px;">
                            <div style="font-size:17px; font-weight:bold;">{{ config('etablissement.nom') }}</div>
                            <div style="font-size:12px; opacity:.85;">{{ config('etablissement.sigle') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <h1 style="margin:0 0 14px; font-size:18px; color:#065f46;">{{ $sujet }}</h1>

                            <p style="margin:0 0 14px; line-height:1.6;">
                                Bonjour {{ $destinataire }},<br>{{ $introduction }}
                            </p>

                            @if ($faits)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                       style="border:1px solid #e5e7eb; border-radius:8px; margin:0 0 16px;">
                                    @foreach ($faits as $intitule => $valeur)
                                        <tr>
                                            <td style="padding:8px 12px; font-size:12px; text-transform:uppercase;
                                                       letter-spacing:.04em; color:#6b7280; border-bottom:1px solid #f3f4f6; width:40%;">
                                                {{ $intitule }}
                                            </td>
                                            <td style="padding:8px 12px; font-size:14px; color:#111827;
                                                       border-bottom:1px solid #f3f4f6;">
                                                {{ $valeur }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            @if ($remarque)
                                <p style="margin:0 0 16px; padding:12px 14px; background:#fef3c7; border-radius:8px;
                                          line-height:1.6; color:#92400e;">{{ $remarque }}</p>
                            @endif

                            @if ($bouton)
                                <p style="margin:0 0 8px;">
                                    <a href="{{ $bouton['url'] }}"
                                       style="display:inline-block; background:#047857; color:#ffffff; text-decoration:none;
                                              padding:11px 22px; border-radius:8px; font-weight:600;">{{ $bouton['libelle'] }}</a>
                                </p>
                                <p style="margin:0; font-size:12px; color:#6b7280; word-break:break-all;">{{ $bouton['url'] }}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px; background:#f8fafc; font-size:12px; color:#6b7280;">
                            Message automatique — {{ config('etablissement.nom') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
