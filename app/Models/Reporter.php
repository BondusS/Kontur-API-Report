<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporter{

    public string $key; // Ключ для работы с API
    public string $inn; // ИНН организации, к которой формируется отчёт
    
    // Конструктор объекта класса
    public function __construct(string $key, string $inn){
        $this->key = $key;
        $this->inn = $inn;
    }

    // Получение json отчёта в виде массива
    public function returndata(){
        $url = 'https://focus-api.kontur.ru/api3/req'; // Основа адреса запроса
        $options = array( 
            'key' => $this->key,
            'inn' => $this->inn
        );
        $adres = $url.'?'.http_build_query($options); // Сформированный адрес запроса
        $response = file_get_contents($adres); // Полученный json отчёт
        $data = json_decode($response, true); // Отчёт в виде массива
        return $data;
    }

    // Получение отчёта в виде html
    public function getOrganizationReqHTML($reqRawData): string {
        $result = '<h3>ИНН '.$reqRawData['inn'].'</h3>'.PHP_EOL;
        if (isset($reqRawData['IP'])) {
            $result .= '<h4>Информация об индивидуальном предпринимателе</h4>'.PHP_EOL;
            $data = $this->getOrganizationReqIndividualData($reqRawData['IP']);
        } 
        else {
            $result .= '<h4>Информация о юридическом лице</h4>'.PHP_EOL;
            $data = $this->getOrganizationReqLegalData($reqRawData['UL']);
        }
        $result .= '<ul>';
        foreach ($data as $datum) {
            $result .= $this->dataToUL($datum);
        }
        $result .= '</ul>';
        return $result;
    }

    // Отчёт об индивидуальном предпренимателе
    protected function getOrganizationReqIndividualData(array $reqRawData): array {
        return [
            'fio' => [
                'label' => 'ФИО',
                'has_children' => false,
                'value' => $reqRawData['fio'] ?? null,
            ],
            'okpo' => [
                'label' => 'Код ОКПО',
                'has_children' => false,
                'value' => $reqRawData['okpo'] ?? null,
            ],
            'okato' => [
                'label' => 'Код ОКАТО',
                'has_children' => false,
                'value' => $reqRawData['okato'] ?? null,
            ],
            'okfs' => [
                'label' => 'Код ОКФС',
                'has_children' => false,
                'value' => $reqRawData['okfs'] ?? null,
            ],
            'okogu' => [
                'label' => 'Код ОКОГУ',
                'has_children' => false,
                'value' => $reqRawData['okogu'] ?? null,
            ],
            'okopf' => [
                'label' => 'Код ОКОПФ',
                'has_children' => false,
                'value' => $reqRawData['okopf'] ?? null,
            ],
            'opf' => [
                'label' => 'Наименование организационно-правовой формы',
                'has_children' => false,
                'value' => $reqRawData['opf'] ?? null,
            ],
            'oktmo' => [
                'label' => 'Код ОКТМО',
                'has_children' => false,
                'value' => $reqRawData['oktmo'] ?? null,
            ],
            'registrationDate' => [
                'label' => 'Дата образования',
                'has_children' => false,
                'value' => $reqRawData['registrationDate'] ?? null,
            ],
            'dissolutionDate' => [
                'label' => 'Дата прекращения деятельности в результате ликвидации, реорганизации или других событий',
                'has_children' => false,
                'value' => $reqRawData['dissolutionDate'] ?? null,
            ],
            'status' => [
                'label' => 'Статус организации',
                'has_children' => true,
                'value' => $this->getStatusData($reqRawData['status']),
            ],
            'pfrRegNumber' => [
                'label' => 'Регистрационный номер ПФР',
                'has_children' => false,
                'value' => $reqRawData['pfrRegNumber'] ?? null,
            ],
            'fssRegNumber' => [
                'label' => 'Регистрационный номер ФСС',
                'has_children' => false,
                'value' => $reqRawData['fssRegNumber'] ?? null,
            ],
            'fomsRegNumber' => [
                'label' => 'Регистрационный номер ФОМС',
                'has_children' => false,
                'value' => $reqRawData['fomsRegNumber'] ?? null,
            ],
            'shortenedAddress' => [
                'label' => 'Информация о местонахождении ИП (может отсутствовать или устареть)',
                'has_children' => true,
                'value' => $this->getParsedAddressRFData($reqRawData['shortenedAddress'] ?? null),
            ],
            'activities' => [
                'label' => 'Виды деятельности',
                'has_children' => true,
                'value' => [
                    'principalActivity' => [
                        'label' => 'Основной вид деятельности',
                        'has_children' => true,
                        'value' => [$this->getActivityValue($reqRawData['activities']['principalActivity'] ?? null)],
                    ],
                    'complementaryActivities' => [
                        'label' => 'Дополнительные виды деятельности',
                        'has_children' => true,
                        'value' => $this->getActivities($reqRawData['activities']['complementaryActivities'] ?? null),
                    ],
                    'okvedVersion' => [
                        'label' => 'Версия справочника ОКВЭД. Значение "2" соответствует ОК 029-2014 (КДЕС Ред. 2), отсутствие поля версии '.
                            'соответствует ОК 029-2001 (КДЕС Ред.1)',
                        'has_children' => false,
                        'value' => $reqRawData['activities']['okvedVersion'] ?? null,
                    ],
                ],
            ],
            'regInfo' => [
                'label' => 'Сведения о регистрации',
                'has_children' => true,
                'value' => $this->getRegInfoData($reqRawData['regInfo'] ?? null),
            ],
            'nalogRegBody' => [
                'label' => 'Сведения о постановке на учет в налоговом органе',
                'has_children' => true,
                'value' => $this->getNalogRegBodyData($reqRawData['nalogRegBody'] ?? null),
            ],
        ];
    }

    // Получение значения логической переменной в локлизованном виде
    protected function boolToRUString(bool $reply) {
        $translation = $reply ? 'Да' : 'Нет';
        return $translation;
    }

    // Получение информации о статусе организации
    protected function getStatusData(?array $status): ?array {
        return [
            'statusString' => [
                'label' => 'Неформализованное описание статуса',
                'has_children' => false,
                'value' => $status['statusString'] ?? null,
            ],
            'reorganizing' => [
                'label' => 'В процессе реорганизации (может прекратить деятельность в результате реорганизации)',
                'has_children' => false,
                'value' => isset($status['reorganizing']) ? $this->boolToRUString($status['reorganizing']) : null,
            ],
            'bankrupting' => [
                'label' => 'В процессе банкротства по данным ЕГРЮЛ (обращаем внимание, что не все организации, находящиеся в процессе '.
                    'банкротства, имеют банкротный статус)',
                'has_children' => false,
                'value' => isset($status['bankrupting']) ? $this->boolToRUString($status['bankrupting']) : null,
            ],
            'dissolving' => [
                'label' => 'В стадии ликвидации (либо планируется исключение из ЕГРЮЛ)',
                'has_children' => false,
                'value' => isset($status['dissolving']) ? $this->boolToRUString($status['dissolving']) : null,
            ],
            'dissolved' => [
                'label' => 'Недействующее',
                'has_children' => false,
                'value' => isset($status['dissolved']) ? $this->boolToRUString($status['dissolved']) : null,
            ],
            'date' => [
                'label' => 'Дата',
                'has_children' => false,
                'value' => $status['date'] ?? null,
            ],
        ];
    }

    // Получение адреса в РФ
    protected function getParsedAddressRFData(?array $legalAddress): ?array {
        if (empty($legalAddress)) {
            return null;
        }
        return [
            'zipCode' => [
                'label' => 'Индекс',
                'has_children' => false,
                'value' => $legalAddress['zipCode'] ?? null,
            ],
            'regionCode' => [
                'label' => 'Код региона',
                'has_children' => false,
                'value' => $legalAddress['regionCode'] ?? null,
            ],
            'regionName' => [
                'label' => 'Регион',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['regionName'] ?? null),
            ],
            'district' => [
                'label' => 'Район',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['district'] ?? null),
            ],
            'city' => [
                'label' => 'Город',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['city'] ?? null),
            ],
            'settlement' => [
                'label' => 'Населенный пункт',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['settlement'] ?? null),
            ],
            'street' => [
                'label' => 'Улица',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['street'] ?? null),
            ],
            'house' => [
                'label' => 'Дом',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['house'] ?? null),
            ],
            'bulk' => [
                'label' => 'Корпус',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['bulk'] ?? null),
            ],
            'flat' => [
                'label' => 'Офис/квартира/комната',
                'has_children' => false,
                'value' => $this->getDataFromToponym($legalAddress['flat'] ?? null),
            ],
        ];
    }

    // Получение информации о топониме
    protected function getDataFromToponym(?array $toponym): ?string {
        if (empty($toponym)) {
            return null;
        }
        return ($toponym['topoFullName'] ?? $toponym['topoShortName'] ?? '').' '.$toponym['topoValue'];
    }

    // Получение информации об основном виде деятельности
    protected function getActivityValue(?array $activity): ?array {
        if (empty($activity)) {
            return null;
        }
        return ['label' => $activity['code'], 'has_children' => false, 'value' => $activity['date'].' - '.$activity['text']];
    }

    // Получение информации об основных видах деятельности
    protected function getActivities(?array $activities): ?array {
        if (empty($activities)) {
            return null;
        }
        return array_map(function ($activity) {
            return $this->getActivityValue($activity);
        }, $activities);
    }

    // Получение сведений о регистрации
    protected function getRegInfoData(?array $regInfo): ?array {
        if (empty($regInfo)) {
            return null;
        }
        return [
            'ogrnDate' => [
                'label' => 'Дата присвоения ОГРН',
                'has_children' => false,
                'value' => $regInfo['ogrnDate'] ?? null,
            ],
            'regName' => [
                'label' => 'Наименование органа, зарегистрировавшего юридическое лицо до 1 июля 2002 года',
                'has_children' => false,
                'value' => $regInfo['regName'] ?? null,
            ],
            'regNum' => [
                'label' => 'Регистрационный номер, присвоенный до 1 июля 2002 года',
                'has_children' => false,
                'value' => $regInfo['regNum'] ?? null,
            ],
        ];
    }

    // Получение сведений о постановке на учет в налоговом органе
    protected function getNalogRegBodyData(?array $nalogRegBody): ?array {
        if (empty($nalogRegBody)) {
            return null;
        }
        return [
            'nalogCode' => [
                'label' => 'Код налогового органа',
                'has_children' => false,
                'value' => $nalogRegBody['nalogCode'] ?? null,
            ],
            'nalogName' => [
                'label' => 'Наименование налогового органа',
                'has_children' => false,
                'value' => $nalogRegBody['nalogName'] ?? null,
            ],
            'nalogRegDate' => [
                'label' => 'Дата постановки на учет',
                'has_children' => false,
                'value' => $nalogRegBody['nalogRegDate'] ?? null,
            ],
            'nalogRegAddress' => [
                'label' => 'Адрес регистрирующего органа',
                'has_children' => false,
                'value' => $nalogRegBody['nalogRegAddress'] ?? null,
            ],
            'kpp' => [
                'label' => 'КПП',
                'has_children' => false,
                'value' => $nalogRegBody['kpp'] ?? null,
            ],
            'date' => [
                'label' => 'Дата',
                'has_children' => false,
                'value' => $nalogRegBody['date'] ?? null,
            ],
        ];
    }

    // Получение информации о юридическом лице
    protected function getOrganizationReqLegalData(array $reqRawData): array {
        return [
            'kpp' => [
                'label' => 'КПП',
                'has_children' => false,
                'value' => $reqRawData['kpp'] ?? null,
            ],
            'okpo' => [
                'label' => 'Код ОКПО',
                'has_children' => false,
                'value' => $reqRawData['okpo'] ?? null,
            ],
            'okato' => [
                'label' => 'Код ОКАТО',
                'has_children' => false,
                'value' => $reqRawData['okato'] ?? null,
            ],
            'okfs' => [
                'label' => 'Код ОКФС',
                'has_children' => false,
                'value' => $reqRawData['okfs'] ?? null,
            ],
            'oktmo' => [
                'label' => 'Код ОКТМО',
                'has_children' => false,
                'value' => $reqRawData['oktmo'] ?? null,
            ],
            'okogu' => [
                'label' => 'Код ОКОГУ',
                'has_children' => false,
                'value' => $reqRawData['okogu'] ?? null,
            ],
            'okopf' => [
                'label' => 'Код ОКОПФ',
                'has_children' => false,
                'value' => $reqRawData['okopf'] ?? null,
            ],
            'opf' => [
                'label' => 'Наименование организационно-правовой формы',
                'has_children' => false,
                'value' => $reqRawData['opf'] ?? null,
            ],
            'legalName' => [
                'label' => 'Наименование юридического лица',
                'has_children' => true,
                'value' => $this->getLegalNameData($reqRawData['legalName']),
            ],
            'legalAddress' => [
                'label' => 'Юридический адрес',
                'has_children' => true,
                'value' => [
                    'parsedAddressRF' => [
                        'label' => 'Разобранный на составляющие адрес в РФ',
                        'has_children' => true,
                        'value' => $this->getParsedAddressRFData($reqRawData['legalAddress']['parsedAddressRF'] ?? null),
                    ],
                    'date' => ['label' => 'Дата',
                        'has_children' => false,
                        'value' => $reqRawData['legalAddress']['date'] ?? null,
                    ],
                    'firstDate' => [
                        'label' => 'Дата первого внесения сведений',
                        'has_children' => false,
                        'value' => $reqRawData['legalAddress']['firstDate'] ?? null,
                    ],
                ],
            ],
            'branches' => [
                'label' => 'Филиалы и представительства',
                'has_children' => true,
                'value' => $this->getBranchesData($reqRawData['branches'] ?? null),
            ],
            'status' => [
                'label' => 'Статус организации',
                'has_children' => true,
                'value' => $this->getStatusData($reqRawData['status']),
            ],
            'registrationDate' => [
                'label' => 'Дата образования',
                'has_children' => false,
                'value' => $reqRawData['registrationDate'] ?? null,
            ],
            'dissolutionDate' => [
                'label' => 'Дата прекращения деятельности в результате ликвидации, реорганизации или других событий',
                'has_children' => false,
                'value' => $reqRawData['dissolutionDate'] ?? null,
            ],
            'heads' => [
                'label' => 'Лица, имеющие право подписи без доверенности (руководители)',
                'has_children' => true,
                'value' => $this->getHeadsData($reqRawData['heads'] ?? null),
            ],
            'managementCompanies' => [
                'label' => 'Управляющие компании',
                'has_children' => true,
                'value' => $this->getManagementCompaniesData($reqRawData['managementCompanies'] ?? null),
            ],
            'activities' => [
                'label' => 'Виды деятельности',
                'has_children' => true,
                'value' => [
                    'principalActivity' => [
                        'label' => 'Основной вид деятельности',
                        'has_children' => true,
                        'value' => [$this->getActivityValue($reqRawData['activities']['principalActivity'] ?? null)],
                    ],
                    'complementaryActivities' => [
                        'label' => 'Дополнительные виды деятельности',
                        'has_children' => true,
                        'value' => $this->getActivities($reqRawData['activities']['complementaryActivities'] ?? null),
                    ],
                    'okvedVersion' => [
                        'label' => 'Версия справочника ОКВЭД. Значение "2" соответствует ОК 029-2014 (КДЕС Ред. 2), отсутствие поля версии '.
                            'соответствует ОК 029-2001 (КДЕС Ред.1)',
                        'has_children' => false,
                        'value' => $reqRawData['activities']['okvedVersion'] ?? null,
                    ],
                ],
            ],
            'regInfo' => [
                'label' => 'Сведения о регистрации',
                'has_children' => true,
                'value' => $this->getRegInfoData($reqRawData['regInfo'] ?? null),
            ],
            'nalogRegBody' => [
                'label' => 'Сведения о постановке на учет в налоговом органе',
                'has_children' => true,
                'value' => $this->getNalogRegBodyData($reqRawData['nalogRegBody'] ?? null),
            ],
            'registrarOfShareholders' => [
                'label' => 'Сведения о держателе реестра акционеров акционерного общества',
                'has_children' => true,
                'value' => $this->getRegistrarOfShareholdersData($reqRawData['registrarOfShareholders'] ?? null),
            ],
        ];
        return [];
    }

    // Получение наименования юр лица
    protected function getLegalNameData(?array $legalName): ?array {
        return [
            'short' => [
                'label' => 'Краткое наименование организации',
                'has_children' => false,
                'value' => $legalName['short'] ?? null,
            ],
            'full' => [
                'label' => 'Полное наименование организации',
                'has_children' => false,
                'value' => $legalName['full'] ?? null,
            ],
            'readable' => [
                'label' => 'Полное наименование, приведенное к нижнему регистру с сокращением аббревиатур',
                'has_children' => false,
                'value' => $legalName['readable'] ?? null,
            ],
            'date' => [
                'label' => 'Дата',
                'has_children' => false,
                'value' => $legalName['date'] ?? null,
            ],
        ];
    }

    // Получение информации о филиалах и представительствах
    protected function getBranchesData(?array $branches): ?array {
        if (empty($branches)) {
            return null;
        }
        return array_map(function ($branch) {
            return [
                'label' => 'Наименование филиала или представительства - <strong>'.
                    ($branch['name'] ?? 'Данные о наименовании филиала/представительства отсутствует').'</strong>',
                'has_children' => true,
                'value' => [
                    'kpp' => [
                        'label' => 'КПП',
                        'has_children' => false,
                        'value' => $branch['kpp'] ?? null,
                    ],
                    'parsedAddressRF' => [
                        'label' => 'Разобранный на составляющие адрес в РФ',
                        'has_children' => true,
                        'value' => $this->getParsedAddressRFData($branch['parsedAddressRF'] ?? null),
                    ],
                    'foreignAddress' => [
                        'label' => 'Адрес вне РФ',
                        'has_children' => true,
                        'value' => $this->getForeignAddressData($branch['foreignAddress'] ?? null),
                    ],
                    'date' => [
                        'label' => 'Дата',
                        'has_children' => false,
                        'value' => $branch['date'] ?? null,
                    ],
                ],
            ];
        }, $branches);
    }

    // Получение адреса вне рф
    protected function getForeignAddressData(?array $foreignAddress): ?array {
        if (empty($foreignAddress)) {
            return null;
        }
        return [
            'countryName' => [
                'label' => 'Наименование страны',
                'has_children' => false,
                'value' => $foreignAddress['countryName'] ?? null,
            ],
            'addressString' => [
                'label' => 'Строка, содержащая адрес',
                'has_children' => false,
                'value' => $foreignAddress['addressString'] ?? null,
            ],
        ];
    }

    // Получение информации о руководителях
    protected function getHeadsData(?array $heads): ?array {
        if (empty($heads)) {
            return null;
        }
        return array_map(static function ($head) {
            return [
                'label' => '<strong>'.$head['fio'].'</strong>',
                'has_children' => true,
                'value' => [
                    'innfl' => [
                        'label' => 'ИННФЛ',
                        'has_children' => false,
                        'value' => $head['innfl'] ?? null,
                    ],
                    'position' => [
                        'label' => 'Должность',
                        'has_children' => false,
                        'value' => $head['position'] ?? null,
                    ],
                    'date' => [
                        'label' => 'Дата последнего внесения изменений',
                        'has_children' => false,
                        'value' => $head['date'] ?? null,
                    ],
                    'firstDate' => [
                        'label' => 'Дата первого внесения сведений',
                        'has_children' => false,
                        'value' => $head['firstDate'] ?? null,
                    ],
                ],
            ];
        }, $heads);
    }

    // Получение информаци об управляющих
    protected function getManagementCompaniesData(?array $managementCompanies): ?array {
        if (empty($managementCompanies)) {
            return null;
        }
        return array_map(static function ($managementCompany) {
            return [
                'label' => '<strong>'.$managementCompany['name'].'</strong>',
                'has_children' => true,
                'value' => [
                    'inn' => [
                        'label' => 'ИНН управляющей организации',
                        'has_children' => false,
                        'value' => $managementCompany['inn'] ?? null,
                    ],
                    'ogrn' => [
                        'label' => 'ОГРН управляющей организации',
                        'has_children' => false,
                        'value' => $managementCompany['ogrn'] ?? null,
                    ],
                    'date' => [
                        'label' => 'Дата последнего внесения изменений',
                        'has_children' => false,
                        'value' => $managementCompany['date'] ?? null,
                    ],
                    'firstDate' => [
                        'label' => 'Дата первого внесения сведений',
                        'has_children' => false,
                        'value' => $managementCompany['firstDate'] ?? null,
                    ],
                ],
            ];
        }, $managementCompanies);
    }

    // Получение cведений о держателе реестра акционеров акционерного общества
    protected function getRegistrarOfShareholdersData(?array $registrarOfShareholders): ?array {
        if (empty($registrarOfShareholders)) {
            return null;
        }
        return [
            'name' => [
                'label' => 'Наименование держателя реестра акционеров',
                'has_children' => false,
                'value' => $registrarOfShareholders['name'] ?? null,
            ],
            'inn' => [
                'label' => 'ИНН держателя реестра акционеров (если указан)',
                'has_children' => false,
                'value' => $registrarOfShareholders['inn'] ?? null,
            ],
            'ogrn' => [
                'label' => 'ОГРН держателя реестра акционеров (если указан)',
                'has_children' => false,
                'value' => $registrarOfShareholders['ogrn'] ?? null,
            ],
            'date' => [
                'label' => 'Дата последнего внесения изменений',
                'has_children' => false,
                'value' => $registrarOfShareholders['date'] ?? null,
            ],
            'firstDate' => [
                'label' => 'Дата первого внесения сведений',
                'has_children' => false,
                'value' => $registrarOfShareholders['firstDate'] ?? null,
            ],
        ];
    }

    // Формирование из данных html кода
    protected function dataToUL(array $data): string {
        if (! empty($data['has_children'])) {
            if ($data['value'] === null) {
                return '';
            }
            $result = '<li>'.$data['label'].':</li>'.PHP_EOL;
            $result .= '<ul>'.PHP_EOL;
            foreach ($data['value'] as $datum) {
                if($datum != null) {
                    $result .= $this->dataToUL($datum);
                }
            }
            $result .= '</ul>'.PHP_EOL;
            return $result;
        }
        return $data['value'] !== null ? '<li>'.$data['label'].' - <strong>'.$data['value'].'</strong></li>'.PHP_EOL:'';
    }
}
