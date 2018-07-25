<?php

/**
 * Created by PhpStorm.
 * User: test
 * Date: 24.07.18
 * Time: 23:40
 * Это не совсем классическая бинарная сортировка, т.к. не задан фиксированный размер строки => все преимущества
 * данного вида сортировки - неактуальны.
 */
class fileBinSearch
{
    // Задаем размер блока, который будем считывать
    private $recBlockSize = 256;

    /*
     * Шифт по файлу, чтобы найти начало строки
     */
    private function shiftRecord($file)
    {
        while (true) {

            $ofs = ftell($file);
            $r = $ofs > $this->recBlockSize ? $this->recBlockSize : $ofs;

            rewind($file);
            $tmp = $ofs - $r;
            fseek($file, $tmp);
            $buf = fread($file, $r);
            rewind($file);
            fseek($file, $tmp);
            $o = strrpos($buf, "\x0A");

            if ($o >= 0) {

                rewind($file);
                fseek($file, ($tmp + $o +1));

                break;
            } elseif ($r == $ofs) {
                rewind($file);
                break;
            }
        }

    }

    /*
     * Считываем строчку
     */
    private function readRecord($file)
    {
        $line = '';
        $buf = '';

        while (true) {
            $ofs = ftell($file);
            $buf = fread($file, $this->recBlockSize);
            $o = stripos($buf, "\x0A");

            // если нашли, то
            if ($o >= 0) {
                $tt = $ofs + $o + 1;

                fseek($file, $tt);
                $line .= substr($buf, 0, $o);

                return preg_split("/\t/", $line);
            } else {
                $line .= $buf;
            }
        }

        return false;
    }

    /*
     * Функция бинарного поиска
     *
     */
    private function binSearch($file, $findKey, $fileBegin, $fileEnd)
    {
        if ($fileBegin == $fileEnd ) {
            echo 'undef';
            die();
        }

        $fileMid = floor(($fileBegin + $fileEnd) / 2);


        if (fseek($file, $fileMid) == -1){
            echo 'undef';
            die();

        }
        $this->shiftRecord($file);

        $fileMid = ftell($file);

        // Центральная запись
        $kv = $this->readRecord($file);

        if (strnatcasecmp($kv['0'], $findKey) == 0) {

            echo $kv['1'];
            die();
        }

        //Рекурсия >
        if (strnatcasecmp($kv['0'], $findKey) < 0) {

            $this->binSearch($file, $findKey, ftell($file), $fileEnd);

        } else {
            $this->binSearch($file, $findKey, $fileBegin, $fileMid);
        }
    }

    /*
     * Функция, которая требовалась по тз
     * Аргументы: имя файла, значение ключа
     */
    public function getData($fileName, $key)
    {
        //Проверяем файл на чтение
        if (!is_readable($fileName))
            die ("Невозможно прочитать файл " . $fileName);


        $fileSize = filesize($fileName);
        $file = fopen($fileName, "r");

        return $this->binSearch($file, $key, 0, $fileSize);
    }

    /*
     * Конструктор
     */
    function __construct()
    {

    }

}