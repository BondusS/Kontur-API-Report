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

    // Получение html отчёта
    public function getOrganizationReqHTML($JsonData){
        $result = '<h3>ИНН '.$JsonData['inn'].'</h3>'.PHP_EOL;
        if (isset($JsonData['IP'])) {
            $result .= '<h4>Информация об индивидуальном предпринимателе</h4>'.PHP_EOL;
            $data = $JsonData['IP'];
        }
        else {
            $result .= '<h4>Информация о юридическом лице</h4>'.PHP_EOL;
            $data = $JsonData['UL'];
        }
        $result .= '<ul>';
        $result .= $this->PrintingDataAsList($data);
        $result .= '</ul>';
        return $result;
    }

    // Вывод данных в виде списка
    public function PrintingDataAsList($data){
        $ListResult = '';
        foreach($data as $key => $value){
            if(gettype($value) == 'array'){
                if(gettype($key) == 'integer'){
                    $ListResult .= '<li>'.($key+1).' :</li>'.PHP_EOL;
                }
                else{
                $ListResult .= '<li>'.$this->KeyRuDictionary[$key].':</li>'.PHP_EOL;
                }
                $ListResult .= '<ul>';
                $ListResult .= $this->PrintingDataAsList($value);
                $ListResult .= '</ul>';
            }
            else if(gettype($value) == 'boolean'){
                $ListResult .= '<li>'.$this->KeyRuDictionary[$key].' - '.$this->boolToRUString($value).'</li>'.PHP_EOL;
            }
            else{
                $ListResult .= '<li>'.$this->KeyRuDictionary[$key].' - '.$value.'</li>'.PHP_EOL; 
            }
        }
        return $ListResult;
    }

    // Получение значения логической переменной в локлизованном виде
    protected function boolToRUString(bool $reply) {
        $translation = $reply ? 'Да' : 'Нет';
        return $translation;
    }

    // Словарь ключей json массива
    public array $KeyRuDictionary = [
        'fio' => 'ФИО',
        'okpo' => 'Код ОКПО',
        'okato' => 'Код ОКАТО',
        'okfs' => 'Код ОКФС',
        'okogu' => 'Код ОКОГУ',
        'okopf' => 'Код ОКОПФ',
        'opf' => 'Наименование организационно-правовой формы',
        'oktmo' => 'Код ОКТМО',
        'registrationDate' => 'Дата образования',
        'dissolutionDate' => 'Дата прекращения деятельности в результате ликвидации, реорганизации или других событий',
        'status' => 'Статус организации',
        'pfrRegNumber' => 'Регистрационный номер ПФР',
        'fssRegNumber' => 'Регистрационный номер ФСС',
        'fomsRegNumber' => 'Регистрационный номер ФОМС',
        'shortenedAddress' => 'Информация о местонахождении ИП (может отсутствовать или устареть)',
        'activities' => 'Виды деятельности',
        'principalActivity' => 'Основной вид деятельности',
        'complementaryActivities' => 'Дополнительные виды деятельности',
        'okvedVersion' => 'Версия справочника ОКВЭД. Значение "2" соответствует ОК 029-2014 (КДЕС Ред. 2), отсутствие поля версии соответствует ОК 029-2001 (КДЕС Ред.1)',
        'regInfo' => 'Сведения о регистрации',
        'nalogRegBody' => 'Сведения о постановке на учет в налоговом органе',
        'statusString' => 'Неформализованное описание статуса',
        'reorganizing' => 'В процессе реорганизации (может прекратить деятельность в результате реорганизации)',
        'bankrupting' => 'В процессе банкротства по данным ЕГРЮЛ (обращаем внимание, что не все организации, находящиеся в процессе банкротства, имеют банкротный статус)',
        'dissolving' => 'В стадии ликвидации (либо планируется исключение из ЕГРЮЛ)',
        'dissolved' => 'Недействующее',
        'date' => 'Дата',
        'zipCode' => 'Индекс',
        'regionCode' => 'Код региона',
        'regionName' => 'Регион',
        'district' => 'Район',
        'city' => 'Город',
        'settlement' => 'Населенный пункт',
        'street' => 'Улица',
        'house' => 'Дом',
        'bulk' => 'Корпус',
        'flat' => 'Офис/квартира/комната',
        'ogrnDate' => 'Дата присвоения ОГРН',
        'regName' => 'Наименование органа, зарегистрировавшего юридическое лицо до 1 июля 2002 года',
        'regNum' => 'Регистрационный номер, присвоенный до 1 июля 2002 года',
        'nalogCode' => 'Код налогового органа',
        'nalogName' => 'Наименование налогового органа',
        'nalogRegDate' => 'Дата постановки на учет',
        'nalogRegAddress' => 'Адрес регистрирующего органа',
        'kpp' => 'КПП',
        'date' => 'Дата',
        'legalName' => 'Наименование юридического лица',
        'legalAddress' => 'Юридический адрес',
        'parsedAddressRF' => 'Разобранный на составляющие адрес в РФ',
        'firstDate' => 'Дата первого внесения сведений',
        'branches' => 'Филиалы и представительства',
        'status' => 'Статус организации',
        'registrationDate' => 'Дата образования',
        'dissolutionDate' => 'Дата прекращения деятельности в результате ликвидации, реорганизации или других событий',
        'heads' => 'Лица, имеющие право подписи без доверенности (руководители)',
        'managementCompanies' => 'Управляющие компании',
        'activities' => 'Виды деятельности',
        'principalActivity' => 'Основной вид деятельности',
        'complementaryActivities' => 'Дополнительные виды деятельности',
        'okvedVersion' => 'Версия справочника ОКВЭД. Значение "2" соответствует ОК 029-2014 (КДЕС Ред. 2), отсутствие поля версии соответствует ОК 029-2001 (КДЕС Ред.1)',
        'registrarOfShareholders' => 'Сведения о держателе реестра акционеров акционерного общества',
        'short' => 'Краткое наименование организации',
        'full' => 'Полное наименование организации',
        'readable' => 'Полное наименование, приведенное к нижнему регистру с сокращением аббревиатур',
        'name' => 'Наименование',
        'foreignAddress' => 'Адрес вне РФ',
        'countryName' => 'Наименование страны',
        'addressString' => 'Строка, содержащая адрес',
        'innfl' => 'ИННФЛ',
        'position' => 'Должность',
        'kladrCode' => 'Код КЛАДР',
        'topoShortName' => 'Краткое наименование вида топонима',
        'topoFullName' => 'Полное наименование вида топонима',
        'topoValue' => 'Значение топонима',
        'bulkRaw' => 'Полное значение поля "Корпус" из ЕГРЮЛ',
        'houseRaw' => 'Полное значение поля "Дом" из ЕГРЮЛ',
        'flatRaw' => 'Полное значение поля "Квартира" из ЕГРЮЛ',
        'isConverted' => 'Адрес сконвертирован Фокусом из адреса муниципального деления, указанного в выписке ЕГРЮЛ, в административное деление с использованием базы ФИАС ГАР',
        'structuredFio' => 'Структурированное ФИО',
        'firstName' => 'Имя',
        'lastName' => 'Фамилия',
        'middleName' => 'Отчество',
        'history' => 'История',
        'kpps' => 'КПП',
        'legalNames' => 'Наименование юридического лица',
        'legalAddresses' => 'Список юридических адресов из истории',
        'inn' => 'ИНН',
        'ogrn' => 'ОГРН',
        'isInaccuracy' => 'В ЕГРЮЛ указан признак недостоверности сведений',
        'inaccuracyDate' => 'Дата указания признака недостоверности сведений'
    ];
}