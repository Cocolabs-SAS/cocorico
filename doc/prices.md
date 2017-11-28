# Prices

All prices (listing, booking, bankwire, refund, ...) are stored in cents and in the default app currency 
defined by `cocorico.currency` parameter.

Entities decimal prices are accessed though `getXXXDecimal` methods.


## VAT

Listing price fixing can be set with or without VAT included depending on the parameter `cocorico.include_vat` value.

If it's setted to true then:

- listing price fixing include VAT
- all other prices like booking, bank wire, ... include also VAT

If it's setted to false then:

- listing price fixing don't include VAT
- Most of asker relative prices are displayed including VAT
- Most of offerer relative prices are displayed excluding VAT


## Fees

The platform can take fees on each transactions. 
Fees rate are defined by the parameters `cocorico.fee_as_asker` and `cocorico.fee_as_offerer` parameter. 

The administrator can choose to change the fee rate of each user as asker and as offerer. 


## Refund

There are two type of cancellation policies **Flexible** and **Strict**. 
Each policy define how asker will be refunded according to when he make a cancelation.

These rules are defined by the parameter `cocorico.booking.cancelation_policy`.

Example:

- Initial amounts:
    - Booking amount excl fees = 95€
    - Asker fees = 10€
    - Offerer fees = 5€
    - Amount payed by asker = 110€
        
- Amount refunded is 100%: Offerer fees payed by asker are refunded to asker.
    - Amount refunded to asker = 95€ * 1 + 5€ = 100€
    - Amount transferred to offerer wallet = 95€ * (1 - 1)  = 0€
    - Fees taken by the platform = 10€
    
- Amount refunded is 50%: No fees refunded
    - Amount refunded to asker = 95€ * 0.5  = 47.50€
    - Amount transferred to offerer wallet = 95€ * (1 - 0.5)  = 47.50€
    - Fees taken by the platform = 15€

- Amount refunded is 0%: No fees refunded
    - Amount refunded to asker = 95€ * 0 = 0€
    - Amount transferred to offerer wallet = 95€ * (1 - 0) = 95€
    - Fees taken by the platform = 15€
