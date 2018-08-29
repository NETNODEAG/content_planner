<?php

namespace Drupal\content_calendar;

abstract class DateTimeHelper {

  static $formatMYSQLDateOnlyRegex = '\d{4}\-\d{2}\-\d{2}';
  static $formatMYSQLDateOnly = 'Y-m-d';

  /**
   * Get Month label by its number
   *
   * @param int $number
   *
   * @return bool|mixed
   */
  public static function getMonthLabelByNumber($number) {

    if(is_numeric($number) && ($number >= 1 && $number <= 12)) {

      $month_labels = array(
        1 => t('January'),
        2 => t('February'),
        3 => t('March'),
        4 => t('April'),
        5 => t('May'),
        6 => t('June'),
        7 => t('July'),
        8 => t('August'),
        9 => t('September'),
        10 => t('October'),
        11 => t('November'),
        12 => t('December'),
      );

      return $month_labels[$number];
    }

    return FALSE;
  }

  /**
   * Get the count of days in a given month of a given year
   *
   * @param int $month
   * @param int $year
   *
   * @return int
   */
  public static function getDayCountInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
  }

  /**
   * Get the first day of a given month and year
   *
   * @param int $month
   * @param int $year
   *
   * @return \DateTime
   */
  public static function getFirstDayOfMonth($month, $year) {
    $datetime = new \DateTime();

    $datetime->setDate($year, $month, 1);
    $datetime->setTime(0, 0, 0);

    return $datetime;
  }

  /**
   * @param int $month
   * @param int $year
   *
   * @return \DateTime
   */
  public static function getLastDayOfMonth($month, $year) {

    $datetime = new \DateTime();

    $datetime->setDate($year, $month, 1);
    $datetime->setTime(23, 59, 59);

    $datetime->modify('last day of this month');

    return $datetime;
  }

  /**
   * Convert unix timestamp to Datetime object
   *
   * @param int $unix_timestamp
   *
   * @return \DateTime
   */
  public static function convertUnixTimestampToDatetime($unix_timestamp) {

    $datetime = new \DateTime();
    $datetime->setTimestamp($unix_timestamp);

    return $datetime;
  }

  /**
   * Check is a given string is a date of the MySQL Date Only format
   *
   * @param string $value
   *
   * @return false|int
   */
  public static function dateIsMySQLDateOnly($value) {
    return preg_match("/" . self::$formatMYSQLDateOnlyRegex . "/", $value);
  }

}