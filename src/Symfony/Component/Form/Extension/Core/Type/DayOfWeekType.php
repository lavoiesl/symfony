<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class DayOfWeekType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        //  IntlCalendar::DOW_* constants
        $daysOfWeek = array(
            \IntlCalendar::DOW_SUNDAY    => 'sunday',
            \IntlCalendar::DOW_MONDAY    => 'monday',
            \IntlCalendar::DOW_TUESDAY   => 'tuesday',
            \IntlCalendar::DOW_WEDNESDAY => 'wednesday',
            \IntlCalendar::DOW_THURSDAY  => 'thursday',
            \IntlCalendar::DOW_FRIDAY    => 'friday',
            \IntlCalendar::DOW_SATURDAY  => 'saturday',
        );

        /**
         * Goes through all daysOfWeek once, starting at $firstDayOfWeek
         *
         * @param int $firstDayOfWeek
         * @return array
         */
        $getOrderedDaysOfWeek = function($firstDayOfWeek) use ($daysOfWeek) {
            $days = array();

            // goes through all items once, starting at $firstDayOfWeek
            for ($i = $firstDayOfWeek - 1; $i < ($firstDayOfWeek + 6); $i++) { 
                $day = $daysOfWeek[($i % 7) + 1];
                $days[$day] = $day;
            }

            return $days;
        };

        /**
         * Replace values with the formatted day of the week.
         * Uses strtotime('monday')
         *
         * @param IntlDateFormatter $formatter
         * @param array $days
         * @return array
         */
        $getDayNames = function(\IntlDateFormatter $formatter, array $days) {
            $names = null;
            $timestamps = array_map('strtotime', array_keys($days));
            $names = array_map(array($formatter, 'format'), $timestamps);

            return array_combine(array_keys($days), $names);
        };

        $resolver->setDefaults(array(
            'timezone' => null,

            'locale' => \Locale::getDefault(),

            'calendar' => function(Options $options) {
                return \IntlCalendar::createInstance($options['timezone'], $options['locale']);
            },

            'firstdow' => function (Options $options) {
                return $options['calendar']->getFirstDayOfWeek();
            },

            'label_format' => 'eeee',

            'formatter' => function (Options $options) {
                return new \IntlDateFormatter(
                    $options['locale'],
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::NONE,
                    $options['timezone'],
                    $options['calendar'],
                    $options['label_format']
                );
            },

            'choices' => function (Options $options) use ($getOrderedDaysOfWeek, $getDayNames) {
                $days = $getOrderedDaysOfWeek($options['firstdow']);

                return $getDayNames($options['formatter'], $days);
            },
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dayofweek';
    }
}
