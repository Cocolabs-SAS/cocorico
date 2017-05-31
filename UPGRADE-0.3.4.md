UPGRADE to 0.3.4
================

# Table of Contents

- [Categories](#categories)

### Categories

 * The listing categories field has changed. While updating to version 0.3.4 the doctrine schema update command need to 
   be executed once again to resolve the doctrine error:       
                                                                                           
        [Doctrine\DBAL\Exception\DriverException]                                                                            
        An exception occurred while executing 'ALTER TABLE listing_listing_category ADD id INT AUTO_INCREMENT NOT NULL':     
        SQLSTATE[42000]: Syntax error or access violation: 1075 Un seul champ automatique est permis et il doit ├¬tre index├®  
                                                      