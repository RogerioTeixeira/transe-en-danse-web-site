<?php
/**
 * Conditional logic of the condition type Date Time.
 *
 * @package Stackable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'stackable_conditional_display/date-time', function( $condition_is_met, $condition, $block_content, $block ) {
    $options = isset( $condition['options'] ) ? $condition['options'] : null;

    $startDate = isset( $options['start'] ) ? $options['start'] : null;
    $endDate = isset( $options['end'] ) ? $options['end'] : null;

    $days_of_the_week = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' );
    $days_selected = array();
    foreach ( $days_of_the_week as $index => $value ) {
        // Create an array of days selected
        if ( is_array( $options ) && array_key_exists( $value, $options ) && $options[ $value ] ) {
            array_push( $days_selected, $value );
        }
    }

    // Skip checking and show the block if start, end date and days are not set
    if ( ! $days_selected && ! $startDate && ! $endDate ) {
        return true;
    }

    // Get the timezone from the WP admin settings.
    $timezone_string = get_option( 'timezone_string' );
    $gmt_offset = get_option( 'gmt_offset', 0 );

    // Set the timezone string as the timezone if it exists
    if ( $timezone_string ) {
        $timezone = $timezone_string;
    }

    if ( $gmt_offset === 0 ) {
        $timezone = 'UTC';
    } else {
        // Set the timezone as the gmt offset
        $timezone = $gmt_offset;
        // Check if the first char does not have the + or -
        // Append the + sign before the offset
        if (
            isset( $gmt_offset[0] ) &&
            $gmt_offset[0] !== '-' &&
            $gmt_offset[0] !== '+'
        ) {
            $timezone = '+' . $gmt_offset;
        }
    }

	// Converts timezone to a compatible string format if timezone is based on a named location.
	// Timezone "0" or "8" both produce an error, but "+0" and "+8" work.
	if ( is_float( $timezone ) ) {
		if ( $timezone < 0 ) {
			$timezone = strval( $timezone );
		} else {
			$timezone = "+" . strval( $timezone );
		}
	}

	// When the timezone is set to a named location, $timezone is a float;
	// if the timezone is set to a manual UTC offset, $timezone is a string.
	// Convert $timezone from a decimal hour offset (e.g., +8.75) to an hour/minute format (e.g., +08:45)
	if ( ( is_string( $timezone ) && strpos( $timezone, '.' ) !== false ) ||
		( is_float( $timezone ) && strpos( strval( $timezone ), '.' ) !== false )
	 ) {
			$sign = ( $gmt_offset >= 0 ) ? '+' : '-';
			$abs_offset = abs( $gmt_offset );
			$hours = floor( $abs_offset );
			$minutes = round( ( $abs_offset - $hours ) * 60 );

			// Pad hours/minutes to 2-digits
			$hours_str = str_pad( $hours, 2, '0', STR_PAD_LEFT );
			$minutes_str = str_pad( $minutes, 2, '0', STR_PAD_LEFT );

			$timezone = "{$sign}{$hours_str}:{$minutes_str}";
	 }

    // Get the current date time based on WP admin settings.
    $current = current_datetime();

    // Convert the dates to a DateTime object.
    // Returns the current datetime if no startDate / endDate set.
    $start_datetime = new DateTime( $startDate ?? 'now', new DateTimeZone( $timezone ) );
    $end_datetime = new DateTime( $endDate ?? 'now', new DateTimeZone( $timezone ) );

    // If no endDate set, but there is a startDate set
    if ( ! $endDate && $startDate ) {
        // If the startDate elapsed and there are days selected
        if ( ! empty( $days_selected ) && ( $current > $start_datetime ) ) {
            // Get the current day of the week. Returns a string number (0 for sunday, 1 for monday...)
            $day_num = date( 'w' );
            $match = in_array( $days_of_the_week[ (int)$day_num ], $days_selected );

            return $match;
		}

        return $start_datetime < $current;

	// If no startDate set, but there is an endDate set
    } else if ( ! $startDate && $endDate ) {
        if ( ! empty( $days_selected ) && ( $current < $end_datetime ) ) {
            // Get the current day of the week. Returns a string number (0 for sunday, 1 for monday...)
            $day_num = date( 'w' );
            $match = in_array( $days_of_the_week[ (int)$day_num ], $days_selected );

            return $match;
        }

        return $current < $end_datetime;

    } else if ( $startDate && $endDate ) {
        // If current date is in the middle of start and end date
        if ( ! empty( $days_selected ) && ( $start_datetime < $current ) && ( $current < $end_datetime ) ) {
            // Get the current day of the week. Returns a string number (0 for sunday, 1 for monday...)
            $day_num = date( 'w' );
            $match = in_array( $days_of_the_week[ (int)$day_num ], $days_selected );

            return $match;
        }

        return $start_datetime < $current && $current < $end_datetime;

	// If no start and end date set, only selected days to show the block
    } else if ( ! $startDate && ! $endDate && ! empty( $days_selected ) ) {
        // Get the current day of the week. Returns a string number (0 for sunday, 1 for monday...)
        $day_num = date( 'w' );
        $match = in_array( $days_of_the_week[ (int)$day_num ], $days_selected );

        return $match;
    }

	return $condition_is_met;
}, 10, 5 );
