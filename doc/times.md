# Times

## Time unit

Time unit depend on value of some parameters.
See `Cocorico/CoreBundle/Resources/config/parameters.yml` to view default values.

* Day mode

        cocorico.time_unit: 1440
        cocorico.time_unit_allday: true

* Night mode:

        cocorico.time_unit: 1440
        cocorico.time_unit_allday: false

* Hour mode:

        cocorico.time_unit: 60
        cocorico.time_unit_allday: true


Here are other time unit relative parameters:

* Allow single day (start day = end day) booking request and listing search. 
    If days_max is set to 1 then must be set to true.

        cocorico.booking.allow_single_day: true
        cocorico.booking.end_day_included: true

* Include end day in booking request and listing search and disable single day booking request and listing search
    If days_max is set to 1 then must be set to true

        cocorico.booking.allow_single_day: false
        cocorico.booking.end_day_included: true

* Days display mode (range or duration)

        cocorico.days_display_mode: duration

* Times display mode (range or duration). No effect if time unit is day

        cocorico.times_display_mode: duration

* Max search, booking time unit number. Min 1. Max value of times max depends on time unit: 24 if time unit is hour.
Not needed if time unit is day.

        cocorico.times_max: 8


Examples:

* Night mode

        cocorico.time_unit: 1440
        cocorico.time_unit_allday: false
        cocorico.booking.allow_single_day: false
        cocorico.booking.end_day_included: false
        cocorico.days_display_mode: duration

* Day mode

        cocorico.time_unit: 1440
        cocorico.time_unit_allday: true
        cocorico.booking.allow_single_day: false
        cocorico.booking.end_day_included: false
        cocorico.days_display_mode: duration

* Hour mode

        cocorico.time_unit: 60
        cocorico.time_unit_flexibility: 8
        cocorico.time_unit_allday: true
        cocorico.days_display_mode: duration
        cocorico.times_display_mode: duration
        cocorico.days_max: 1
        cocorico.times_max: 8
        cocorico.booking.allow_single_day: true
        cocorico.booking.end_day_included: true


## Booking Expiration

Booking expiration depends on the following parameters: 

    cocorico.booking.min_start_time_delay
    cocorico.booking.acceptation_delay
    cocorico.booking.expiration_delay

Note: min_start_time_delay must be >= cocorico.booking.acceptation_delay + 1 hour

Booking acceptation and expiration examples:

        min_start_time_delay: 6h
        expiration_delay: 12h
        acceptation_delay: 4h
        
        new: 10h
        start: 16h
        
        expired: 22h
        accepted: 12h
        
        ---------------------------- blocking
        min_start_time_delay: 6h
        expiration_delay: 4h
        acceptation_delay: 12h
        
        new: 10h
        start: 16h
        
        expired: 14h
        accepted: 4h problem
        ----------------------------
        min_start_time_delay: 12h
        expiration_delay: 6h
        acceptation_delay: 4h
        
        new: 10h
        start: 22h
        
        expired: 16h X
        accepted: 18h
        ----------------------------
        min_start_time_delay: 12h
        expiration_delay: 4h
        acceptation_delay: 6h
        
        new: 10h
        start: 22h
        
        expired: 14h X
        accepted: 16h
        ---------------------------- blocking
        min_start_time_delay: 4h
        expiration_delay: 12h
        acceptation_delay: 6h
        
        new: 10h
        start: 14h
        
        expired: 22h problem
        accepted: 8h problem
        ---------------------------- blocking
        min_start_time_delay: 4h
        expiration_delay: 6h
        acceptation_delay: 12h
        
        new: 10h
        start: 14h
        
        expired: 16h problem
        accepted: 2h problem
        
        -----------------------------------------------------------
        min_start_time_delay: 4h
        expiration_delay: 4h
        acceptation_delay: 3h
        
        new: 10h
        start: 14h
        
        expired: 14h problem
        accepted: 11h
        ---------------------------- blocking
        min_start_time_delay: 4h
        expiration_delay: 3h
        acceptation_delay: 4h
        
        new: 10h
        start: 14h
        
        expired: 13h
        accepted: 10h problem
        ---------------------------- blocking
        min_start_time_delay: 4h
        expiration_delay: 4h
        acceptation_delay: 4h
        
        new: 10h
        start: 14h
        
        expired: 14h problem
        accepted: 10h problem
        ---------------------------- blocking
        min_start_time_delay: 4h
        expiration_delay: 4h
        acceptation_delay: 5h
        
        new: 10h
        start: 14h
        
        expired: 14h problem
        accepted: 9h problem
        ---------------------------- blocking
        min_start_time_delay: 4h
        expiration_delay: 5h
        acceptation_delay: 4h
        
        new: 10h
        start: 14h
        
        expired: 15h problem
        accepted: 10h problem
        
        
        ----------------------------
        min_start_time_delay: 12h
        expiration_delay: 48h
        acceptation_delay: 4h
        
        new: 01/01 01h
        start: 01/01 21h
        
        expired: 03/01 01h problem
        accepted: 01/01 17h
        
        ----------------------------
        min_start_time_delay: 12h
        expiration_delay: 48h
        acceptation_delay: 4h
        
        new: 01/01 01h
        start: 02/01 01h
        
        expired: 03/01 01h problem
        accepted: 01/01 17h
        
        ----------------------------
        min_start_time_delay: 12h
        expiration_delay: 48h
        acceptation_delay: 4h
        
        new: 01/01 01h
        start: 05/01 10h
        
        expired: 03/01 01h
        accepted: 05/01 06h
        
        ----------------------------
        min_start_time_delay: 12h
        expiration_delay: 2h
        acceptation_delay: 4h
        
        new: 10h
        start: 16h
        
        expired: 12h
        accepted: 12h

