<x-mail::message>
# {{ $lead->type->getLabel() }} #{{ $lead->id }}

<x-mail::table>
| | |
|:--|:--|
| Имя | {{ $lead->name ?: '—' }} |
| Телефон | {{ $lead->phone }} |
@if ($lead->email)
| E-mail | {{ $lead->email }} |
@endif
@if ($lead->car)
| Модель | {{ $lead->car->name }} |
@endif
@if ($lead->dealer)
| Дилер | {{ $lead->dealer->tr('name', 'ru') }} |
@endif
@if ($lead->city)
| Город | {{ $lead->city }} |
@endif
@if ($lead->subject)
| Тема | {{ $lead->subject }} |
@endif
@if ($lead->payment_method)
| Способ оплаты | {{ $lead->payment_method }} |
@endif
@if ($lead->preferred_date)
| Желаемая дата | {{ $lead->preferred_date->format('d.m.Y') }} |
@endif
| Язык сайта | {{ strtoupper($lead->locale ?? 'ru') }} |
</x-mail::table>

@if ($lead->message)
**Сообщение:**

{{ $lead->message }}
@endif

<x-mail::button :url="url('/admin/leads/'.$lead->id.'/edit')">
Открыть заявку
</x-mail::button>
</x-mail::message>
