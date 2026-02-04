<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4ade80; padding-bottom: 15px; }
        .title { font-size: 26px; font-weight: bold; color: #111; text-transform: uppercase; }
        .section-title { font-size: 18px; font-weight: bold; margin-top: 20px; margin-bottom: 15px; background-color: #f3f4f6; padding: 8px; border-radius: 4px; }
        .list-item { padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .owned { color: #15803d; font-weight: bold; } /* Verde para o que tem */
        .missing { color: #6b7280; } /* Cinza para o que falta */
        .check { font-family: DejaVu Sans, sans-serif; margin-right: 10px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $recipe['title'] }}</div>
        <div style="font-size: 12px; color: #666; margin-top: 5px;">Lista de Ingredientes e Compras</div>
    </div>

    <div class="section-title">O que você precisa:</div>

    <table style="width: 100%; border-collapse: collapse;">
        @foreach($recipe['ingredients'] as $ing)
            <tr>
                <td class="list-item {{ $ing['has_ingredient'] ? 'owned' : 'missing' }}">
                    <span class="check">{{ $ing['has_ingredient'] ? '✔' : '☐' }}</span>
                    {{ $ing['name'] }}
                </td>
                <td style="text-align: right; font-size: 12px; color: #888; border-bottom: 1px solid #eee;">
                    {{ $ing['has_ingredient'] ? 'Você tem' : 'Comprar' }}
                </td>
            </tr>
        @endforeach
    </table>

    <div class="footer">
        Gerado automaticamente pelo Buscador de Receitas.
    </div>
</body>
</html>
