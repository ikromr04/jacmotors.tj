<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Setting::put([
            'phone' => '+992 44 655 81 81',
            'email' => 'info@jacmotors.tj',
            'lead_emails' => 'info@jacmotors.tj',
            'address' => ['ru' => 'г. Душанбе', 'tg' => 'ш. Душанбе', 'en' => 'Dushanbe'],
            'working_hours' => ['ru' => 'Пн–Сб: 9:00–18:00', 'tg' => 'Дш–Шб: 9:00–18:00', 'en' => 'Mon–Sat: 9:00–18:00'],
            'socials' => [
                'instagram' => 'https://www.instagram.com/jacmotors.tj/',
                'facebook' => 'https://www.facebook.com/people/jacmotorstj/100089136972855/',
                'telegram' => 'https://t.me/jacmotorstj',
            ],
            'google_analytics_id' => 'G-E7RSE9KZ8J',
            'yandex_metrika_id' => '93958305',
        ]);

        if (! Dealer::query()->exists()) {
            Dealer::query()->create([
                'name' => ['ru' => 'Koinot Auto', 'tg' => 'Koinot Auto', 'en' => 'Koinot Auto'],
                'city' => ['ru' => 'Душанбе', 'tg' => 'Душанбе', 'en' => 'Dushanbe'],
                'phone' => '+992 44 655 81 81',
                'email' => 'info@jacmotors.tj',
                'has_sales' => true,
                'has_service' => true,
            ]);
        }

        foreach ($this->pages() as $slug => $page) {
            Page::query()->updateOrCreate(['slug' => $slug], $page);
        }
    }

    private function pages(): array
    {
        return [
            'about' => [
                'title' => ['ru' => 'О компании', 'tg' => 'Дар бораи ширкат', 'en' => 'About us'],
                'meta_description' => [
                    'ru' => 'JAC Motors — китайский автопроизводитель с историей с 1964 года. Официальный дистрибьютор в Таджикистане — Koinot Auto.',
                    'tg' => 'JAC Motors — истеҳсолкунандаи мошинҳои Чин бо таърихи аз соли 1964. Дистрибютори расмӣ дар Тоҷикистон — Koinot Auto.',
                    'en' => 'JAC Motors is a Chinese carmaker with a history dating back to 1964. The official distributor in Tajikistan is Koinot Auto.',
                ],
                'body' => [
                    'ru' => <<<'HTML'
                        <p>Компания JAC Motors (Anhui Jianghuai Automobile Co., Ltd) образована 30 сентября 1999 года на базе автозавода «Цзянхуай» (Hefei Jianghuai Automobile Factory), основанного в 1964 году.</p>
                        <p>JAC Motors — комплексное предприятие, выпускающее под собственным брендом полную гамму автомобилей: лёгкие, среднетоннажные и тяжёлые грузовики, минивэны, легковые автомобили, электромобили, автобусы, специальные шасси, а также коробки передач, двигатели и автокомпоненты. 60-летний опыт позволил компании выйти на мощность более 1 миллиона автомобилей в год с полным циклом производства.</p>
                        <p>Помимо крупнейшего научно-исследовательского центра в Хэфэе, в 2005 году компания открыла зарубежный центр разработок в Турине (Италия), а в 2006 году — в Токио (Япония). Сегодня JAC Motors экспортирует продукцию в Южную Америку, Европу, Африку и Азию, сотрудничает более чем со 120 странами и имеет 19 международных производственных баз.</p>
                        <h2>Наша миссия и ценности</h2>
                        <p>«Думай и твори!» — это подход JAC к созданию автомобиля. Быть созидателем — значит проявлять упорство, силу и прагматизм. JAC видит в изменениях отрасли возможность, делает ставку на собственные исследования и разработки, сохраняет открытость и ориентируется на клиента.</p>
                        <h2>Премии и награды</h2>
                        <h3>Бренд, рекомендованный для экспорта</h3>
                        <p>Награда является «паспортом качества» для китайских экспортных товаров и подтверждает соответствие системы управления качеством международным стандартам.</p>
                        <h3>Национальный знак качества</h3>
                        <p>Китайская премия качества — высшая государственная награда за качество продукции. JAC Motors стала первым национальным автопроизводителем, получившим её.</p>
                        HTML,
                    'en' => <<<'HTML'
                        <p>JAC Motors (Anhui Jianghuai Automobile Co., Ltd) was established on 30 September 1999 on the basis of the Hefei Jianghuai Automobile Factory, founded in 1964.</p>
                        <p>JAC Motors is a diversified manufacturer that produces a full range of vehicles under its own brand: light, medium and heavy trucks, minivans, passenger cars, electric vehicles, buses and special chassis, as well as transmissions, engines and components. Six decades of experience have brought its capacity to more than one million vehicles a year with a full production cycle.</p>
                        <p>In addition to its main R&amp;D centre in Hefei, the company opened design centres in Turin, Italy (2005) and Tokyo, Japan (2006). Today JAC Motors exports to South America, Europe, Africa and Asia, works with more than 120 countries and operates 19 international production bases.</p>
                        <h2>Mission and values</h2>
                        <p>“Think and create!” is JAC’s approach to building cars: persistence, strength and pragmatism, independent research and development, openness and a focus on the customer.</p>
                        <h2>Awards</h2>
                        <h3>Brand recommended for export</h3>
                        <p>This award is a “quality passport” for Chinese export goods, confirming a quality management system that meets international standards.</p>
                        <h3>National Quality Award</h3>
                        <p>The China Quality Award is the country’s highest state award for product quality. JAC Motors was the first national carmaker to receive it.</p>
                        HTML,
                    'tg' => <<<'HTML'
                        <p>Ширкати JAC Motors (Anhui Jianghuai Automobile Co., Ltd) 30 сентябри соли 1999 дар асоси заводи автомобилсозии «Цзянхуай», ки соли 1964 таъсис ёфтааст, ташкил шудааст.</p>
                        <p>JAC Motors корхонаи бисёрсоҳа буда, таҳти бренди худ қатори пурраи мошинҳоро истеҳсол мекунад: боркашҳои сабук, миёна ва вазнин, минивэнҳо, мошинҳои сабукрав, электромобилҳо, автобусҳо, инчунин қуттиҳои интиқол, муҳаррикҳо ва қисмҳои эҳтиётӣ. Таҷрибаи 60-сола ба ширкат имкон дод, ки дар як сол зиёда аз 1 миллион мошин истеҳсол кунад.</p>
                        <p>Ғайр аз маркази асосии илмӣ-тадқиқотӣ дар Хэфэй, ширкат соли 2005 дар Турин (Италия) ва соли 2006 дар Токио (Япония) марказҳои коркард кушод. Имрӯз JAC Motors маҳсулоти худро ба Амрикои Ҷанубӣ, Аврупо, Африқо ва Осиё содир мекунад ва бо зиёда аз 120 кишвар ҳамкорӣ дорад.</p>
                        <h2>Рисолат ва арзишҳои мо</h2>
                        <p>«Фикр кун ва эҷод намо!» — равиши JAC ба сохтани мошин: устуворӣ, қувва ва амалгароӣ, тадқиқоти мустақил, кушодагӣ ва нигаронӣ ба мизоҷ.</p>
                        HTML,
                ],
            ],
            'guarantee' => [
                'title' => ['ru' => 'Гарантия', 'tg' => 'Кафолат', 'en' => 'Warranty'],
                'body' => [
                    'ru' => <<<'HTML'
                        <p>Гарантийный срок на легковые автомобили и весь модельный ряд кроссоверов составляет <strong>150 000 км пробега или 5 лет</strong> (в зависимости от того, что наступит раньше).</p>
                        <p>Гарантийный срок на весь модельный ряд пикапов и электромобили составляет <strong>100 000 км пробега или 3 года</strong> (в зависимости от того, что наступит раньше).</p>
                        <p>Владелец вправе предъявить требования по гарантии, связанные с недостатками автомобиля JAC, за которые отвечает продавец (производитель), только в течение гарантийного срока.</p>
                        <p>Гарантийные обязательства производителя выполняются только при условии сервисного обслуживания автомобиля у официального дилера JAC.</p>
                        HTML,
                    'en' => <<<'HTML'
                        <p>The warranty for passenger cars and the entire crossover range is <strong>150,000 km or 5 years</strong>, whichever comes first.</p>
                        <p>The warranty for the entire pickup range and electric vehicles is <strong>100,000 km or 3 years</strong>, whichever comes first.</p>
                        <p>Warranty claims for defects of a JAC vehicle for which the seller (manufacturer) is responsible may only be made during the warranty period.</p>
                        <p>The manufacturer’s warranty obligations apply only if the vehicle is serviced at an official JAC dealer.</p>
                        HTML,
                    'tg' => <<<'HTML'
                        <p>Мӯҳлати кафолат барои мошинҳои сабукрав ва тамоми қатори кроссоверҳо <strong>150 000 км ё 5 сол</strong> аст (вобаста ба он ки кадомаш пештар фаро мерасад).</p>
                        <p>Мӯҳлати кафолат барои тамоми қатори пикапҳо ва электромобилҳо <strong>100 000 км ё 3 сол</strong> аст (вобаста ба он ки кадомаш пештар фаро мерасад).</p>
                        <p>Соҳиби мошин метавонад талаботи кафолатиро танҳо дар давоми мӯҳлати кафолат пешниҳод намояд.</p>
                        <p>Ӯҳдадориҳои кафолатии истеҳсолкунанда танҳо ҳангоми хизматрасонии мошин дар дилери расмии JAC иҷро мешаванд.</p>
                        HTML,
                ],
            ],
            'privacy' => [
                'title' => ['ru' => 'Политика конфиденциальности', 'tg' => 'Сиёсати махфият', 'en' => 'Privacy policy'],
                'body' => [
                    'ru' => '<p>Отправляя заявку на сайте, вы соглашаетесь на обработку указанных вами персональных данных (имя, телефон, e-mail) с целью связи с вами по вашему обращению. Данные не передаются третьим лицам, за исключением официальных дилеров JAC, обрабатывающих ваше обращение.</p><p>Полный текст политики можно скачать ниже.</p>',
                    'en' => '<p>By submitting a request on this website, you agree to the processing of the personal data you provide (name, phone, email) in order for us to respond to your request. The data is not shared with third parties other than official JAC dealers handling your request.</p>',
                    'tg' => '<p>Бо фиристодани дархост дар сомона, шумо ба коркарди маълумоти шахсии худ (ном, телефон, e-mail) бо мақсади тамос бо шумо розӣ мешавед. Маълумот ба шахсони сеюм дода намешавад, ба истиснои дилерҳои расмии JAC.</p>',
                ],
            ],
        ];
    }
}
