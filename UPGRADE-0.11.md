UPGRADE to 0.11
================

# Table of Contents

- [Timezone](#Timezone)

## Timezone

 * Before migration take care of :
    * how mysql booking date and times are stored relatively to timezone (Normally in Europe/Paris).
    * how mongodb dates and times are stored in db relatively to timezone (Normally in Europe/Paris).

 * Bookings date and time are stored now in UTC

 * Mongodb dates and times are also stored in UTC

 * The users timezones have been added in Booking and User entity. Some sql has to be executed after upgrade:

        UPDATE user set time_zone = 'Europe/Paris';
        Update booking set time_zone_asker = 'Europe/Paris', time_zone_offerer = 'Europe/Paris';

 * The booking start and end date and time have been modified:
     * start and end time has been added to start and end date

        Ex: 2017-10-24 00:00 become  2017-10-24 18:00

     * The booking start date has been added to start time

        Ex: 1970-01-01 10:00 become  2017-10-24 10:00

     * The booking end date of end time is now relative to the start date of start time

        Ex: 1970-01-01 23:00 / 1970-01-02 01:00  become  2017-10-24 23:00 / 2017-10-25 01:00