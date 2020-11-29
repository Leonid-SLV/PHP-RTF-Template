<?php
//   +------------------------------------------------+
//   | PHP RTF TEMPLATE
//   +------------------------------------------------+

//https://www.php.net/manual/ru/function.str-split.php
function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

//Преобразование текста в формат rtf
function text2rtf($data)
  {
    $text = str_split_unicode($data);
    $rtf = '';
    for ($i=0, $length=count($text); $i<$length; $i++)
      {
        $symbol = $text[$i]; //Исходный символ
        //if (mb_check_encoding($symbol, 'UTF-8')===false)
        //  {
        //    echo $symbol;
        //  	echo 'unvalid';
        //  	$symbol = mb_convert_encoding($symbol, 'UTF-8', 'UTF-8');
        //  	echo $symbol;
        //  }
        //echo '=====>'.$symbol.'<===';
        // $symbol = iconv('utf-8', 'windows-1251', $symbol); //Смена кодировки на windows-1251
        $symbol = mb_convert_encoding($symbol, 'windows-1251');
        //echo '=====>'.$symbol.'<===';
        $symbol = ord($symbol); //Возвращает ASCII код символа
        $symbol = dechex($symbol); //Перевод в шестнадцатеричную систему
        $symbol = "\'".$symbol; //Перевод в rtf
        $rtf = $rtf.$symbol;
      }
    return $rtf;
  }

//Созданние шаблона на основе rtf
//Array
//(
//    [0] => Array
//        (
//            [phrase] => }{\rtlch\fcs1 \ab\af42\afs16 \ltrch\fcs0 \b\f42\fs16\ul\cf1\insrsid12535835\charrsid12535835 *$LOGIN*}{\rtlch\fcs1 \ab\af42\afs16 \ltrch\fcs0 \b\f42\fs16\ul\cf1\insrsid14099937\charrsid14099937
//            [variety] => }{
//            [text] => *$LOGIN*
//        )
//
// Таким образом выглядит шабон.
function rtf2template($data)
  {
    // Speeding up via cutting binary data from large rtf's.
	//if (strlen($text) > 1024 * 1024)
	//  {
	//	$text = preg_replace("#[\r\n]#", "", $text);
	//	$text = preg_replace("#[0-9a-f]{128,}#is", "", $text);
	//  }

    // For Unicode escaping
	//$text = str_replace("\\'3f", "?", $text);
	//$text = str_replace("\\'3F", "?", $text);

	$phrase = ''; //Фраза
	$f = 0; //Метка записи фразы
	$template = array(); //Массив всех фраз

    //Читаем фразы для замены. Не самый эффективный метод.
    for ($i = 0, $length = strlen($data); $i<$length; $i++)
     {
       if ($data[$i]=='[') //Признак начала фразы
         {
           $f=1; //Пишем фразу
         }
       if ($data[$i]==']') //Признак конца фразы
         {
           $f=0; //Останавливаем запись
           $template[]['phrase'] = $phrase; //Добавляем фразу в массив
           $phrase = ''; //Очищанм накапливаемую фразу
         }
       if ($data[$i]!='[' & $data[$i]!=']' & $f==1)
         {
           $phrase = $phrase.$data[$i]; //Накаплмваем фразу
         }
     }

    // Распазнаем текст во фразах
    foreach ($template as &$value)
      {
        $f_tag = 0; //Признак начала Управляющего слова
        $f_letter = 0; //Признак буквы в шестнадцатеричной  системе
        $f_space = 0; //Признак пробела
        $letter = ''; //Переменная для набора буквы в шестнадцатеричной  системе
        $text = ''; //Текст в читаемом формате
        $value['variety']=''; //Теги группировки вложенных множеств
        for ($i=0, $length=strlen($value['phrase']); $i<$length; $i++)
          {
            //Тег начала Управляющнго слова
            if ($value['phrase'][$i]=='\\')
              {
                $f_tag = 1;  //Устанавливаем тег
                $f_letter = 0;
                $f_space = 0;
              }
            //Тег начала буквы в 16'ричной системе
            if ($value['phrase'][$i]=="'" & $f_tag==1)
              {
                $f_letter = 1;  //Устанавливаем тег начала буквы
                $f_tag = 0;  //Скидываем управляющий тег
                $f_space = 0;
              }
            //Тег пробела
            if ($value['phrase'][$i]==' ')
              {
                $f_space = 1;  //Устанавливаем тег
                $f_tag = 0;
                $f_letter = 0;
              }
            //Тег спец. символа (это может быть начало новой строки, {, })
            if ($value['phrase'][$i]=='{' OR $value['phrase'][$i]=='}' OR $value['phrase'][$i]=="\n" OR $value['phrase'][$i]==chr(13))
              {
                $f_space = 0;
                $f_tag = 0;
                $f_letter = 0;
                //Определяем, какой тег { } стоит в начале строки, конце. Подсчитываем кол-во тегов.
                if ($value['phrase'][$i]=='{' OR $value['phrase'][$i]=='}')
                  {
                    $value['variety']=$value['variety'].$value['phrase'][$i];
                  }
              }
            //Набор буквы и фразы в шестнадцатеричной системе. Перевод в буквы в читаемый формат.
            if ($value['phrase'][$i]!="'" & $f_tag==0 & $f_space==0 & $f_letter==1)
              {
                $letter = $letter . $value['phrase'][$i];
              }
              else
              {
                if (strlen($letter)!=0)
                  {
                    //Если формат изначально windows-1251, не нужно использовать iconv()
                    if (iconv('windows-1251', 'utf-8', chr(hexdec($letter)))===false)
                      {
                        $text = $text . chr(hexdec($letter));
                      }
                      else
                      {
                      	$text = $text . iconv('windows-1251', 'utf-8', chr(hexdec($letter)));
                      }
                    $letter ='';
                  }
              }
            //После пробела в основном обычный текст, так же накапливаем его
            if ($value['phrase'][$i]!=' ' & $f_tag==0 & $f_letter==0 & $f_space==1)
              {
                $text = $text.$value['phrase'][$i];
              }
          } //Конец цикла фразы

        //Сохраняем текст
        $value['text']=$text;
        //Сокращаем группировку вложенных множеств
        while (strpos($value['variety'],'{}')!==false)
          {
            $value['variety']= str_replace('{}','',$value['variety']);
          }
        //Еслт текст был собран в чистом виде, переносим его в нужную переменную
        if (strlen($value['variety'])==0 & strlen($value['text'])==0 & strlen($value['phrase'])!=0)
          {
            $value['text']=$value['phrase'];
          }
      }


    return ($template);

  }

function rtf_compare($text_1, $text_2)
  {
    //Попытка выравнить кодировки
    if (mb_detect_encoding($text_1,mb_detect_order(),true)!=mb_detect_encoding($text_2,mb_detect_order(),true))
      {
        //Кодировки не равны
        //Имперически вычислели, что нужно поменять вторую строку
        if (mb_detect_encoding($text_2,mb_detect_order(),true)=='UTF-8')
          {
            $text_2 = mb_convert_encoding($text_2, 'windows-1251');
          }
      }

    $text_1 = str_replace(' ', '', $text_1);
    $text_2 = str_replace(' ', '', $text_2);
    $text_1 = trim($text_1);
    $text_2 = trim($text_2);
    //debug
    //echo 'text_1====>'.$text_1; echo chr(13); echo 'text_2====>'.$text_2; echo chr(13);
    //
    if ($text_1==$text_2)
      {
        return true;
      }
      else
      {
        return false;
      }
  }

//Функция - пример, простой замены текста в rtf
function rtf_easy_replace($data, $search, $replace)
  {
    $template = rtf2template($data);

    //Debug
    //print_r($template);

    // Замена текста
    foreach ($template as $value)
      {
        if (rtf_compare($value['text'],$search))
          {
            return str_replace('['.$value['phrase'].']',text2rtf($replace),$data);
         }
      }
  }

?>