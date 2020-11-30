This is how quote notification has been implemented : 

asker flash quote demand - 
    template mails/asker/flash_quote_submitted.txt.twig
    mailer function : sendNewFlashQuoteToAsker
    handler->notifyFlashQuote
    - manager->sendAskerFlashQuoteDemand

asker normal quote demand - ask-demand
    template mails/asker/quote_request_sent.txt.twig
    mailer function : `sendNewQuoteToAsker
    handler->notifyRegularQuote
    - manager->sendAskerQuoteDemand

offerer new quote notif - off-new-quote
    template mails/asker/quote_request_received.txt.twig
    mailer function : sendNewQuoteToOfferer
    - manager->sendEven

offerer quote message
    template mails/offerer/quote_request_received.txt.twig
    mailer function : sendQuoteMessageToOfferer

asker quote message
    template mails/asker/quote_request_received.txt.twig
    mailer function : sendQuoteMessageToAsker


For creation:
    QuoteController calls 
        QuoteBaseFormHandler->notifyRegularQuote
        QuoteBaseFormHandler->notifyFlashQuote
    
    QuoteBaseFormHandler calls
        QuoteManger->notifyQuote($type)

For messages
    dashboard/offerer/quoteController calls
        QuoteManager->notifyQute(ask-msg)

    dashboard/asker/quoteController calls
        QuoteManager->notifyQute(off-msg)
