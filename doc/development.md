# Development


## Adding new file upload

- Check mime type of file before upload
- Add the new upload folder to .gitignore and add inside the new upload folder an empty file .gitkeep
- If the new folder is outside of "web/uploads" folder add it to app/config/rsync_exclude.txt

##  Adding new DB field

- If the field is used in listing search criterias, add a DB index on it.

## Display user contents in pages

- All user text fields (listing description, user description, messages between users, ...) must be filtered in twig 
templates through the twig filter `strip_private_info`.

        Ex: listing_translation.description|strip_private_info
    
## Versioning

- Don't push modifications breaking major functionalities and complicating the app usage.


## Dynamic texts

- Some texts must be added through Twig global parameters and not be hardcoded in twig templates.

        Ex: The phone is setted through cocorico_phone twig global parameter
        

## Twig

- Close all block to not break editor indentation.

        Ex: {% block header_class %}header-green{%- endblock -%} instead  {% block header_class %}header-green
    
        
    
