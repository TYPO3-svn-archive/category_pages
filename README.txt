see doc/manual.sxw for a more detailed manual

Used to display a list of page informations of pages that are categorized with toi_category in the frontend

1)	Install toi categories firs, then category pages

2)	Create some new pages of the category type.
	This can also be a tree of pages, the plugin supports the use of levels.
	
	Example:
	food
	  |----vegetables
	  |----fruits
	stuff that I like
	  |----favorite
	  |----much
	  |----not at all

3)	Assign these categories to the pages you want categorized.
	When you edit the page properties a new field called "category" near at the bottom.
	This works like assigning links etc, just klick the folder icon and select the category
	page you want from the new popup window that appears.
	You can assign multiple categories to one page.
	Please select only pages of the category type.
	
	Example:
	Apples (categories: fruits, favorite)
	Cuecumbers (categories: vegetables, favorite)
	Spinach (categories: vegetables, not at all)

4)	insert the temolate plugin: "list category pages" into your template

5)	use the constant editor to configure the behavior you want:
	Template file	[plugin.category_pages.templateFile]
	Default: typo3conf/ext/category_pages/template.tmpl
	HTML template used to display contents in abstract mode.
	
	Displayed Fields	[plugin.category_pages.displayFields]
	Default: title, abstract, media
	Insert a comma separated list of lowercase field names from the pages table.
	These are the names from the database. you can find out what these names are from the admin
	tool 'DB check':
		Select full search -> advanced search.
		Select 'Page' as the table
		A box with list of available fields appears the fields in the box are named with
		the headlines on the 'edit page properties' page.
		Klick on the names you want.
		The database field names separated by commas will appear in the line above.
		You can copy this comma separated lis of field names directly into the TS config.
		Note: the field names are in most cases different from the headline names!
		
	These fields will be then displayed in the list on your page. 
	Note: equivalent ###markers### must be inserted into the template file.
	The markers must have the same name as the database field you want inserted.
	e.g. ###title### for the title field, ###media### for the files field. etc.
	Note, the uid will always be added and is used to create the special ###typolink### tag
	which creates a link to the page. (e.g. index.php?id=5&L=2).
	Look at the template.tmpl file fore an example.
	Important! if you want an image, upload ONLY ONE gif, jpg or png image to the files section.
	DO NOT add other files, since the plugin does not support multiple files - (yet ;-)).

	Order field	[plugin.category_pages.orderType]
	Default: title
	which field should be used to order the page
	Example: 'title' Display contents ordered by date or title.
	Note: must be the exact same as one of the display field names.


	Order	[plugin.category_pages.orderAsc]
	Default: asc
	Display contents in ascendent or descendant order.

6)	Insert a pagecontent and select "list categorized pages" from the plugin section.
	Insert the categories you want displayed as startingpoing.
	You can also select the level of recursiveness.
	Examples:
	Title: My favorite food
	Startingpoint: favorites
	This will display the title, image and abstract of your apple and cuecumber pages. 
	(if you use the default fields)
	If you enter vegetables as starting point, you will get cuecumbers and spinach etc.
	If you enter food and no recursiveness, you will get nothing since you do not have pages
	that have the food category directly. 
	To get all foods you need to select foods as starting point and set recursive to at least
	1 level. 
	This will give you all the pages that are food (e.g. apples cuecumbers and spinach).
	

Feel free to contact me for any bugs, requests or comments.
Till Korten (webmaster@korten-privat.de)
