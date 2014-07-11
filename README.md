# Maven Algolia

Maven Algolia makes it simple to add Algolia-powered search and autocomplete to your WordPress site.

Posts, Pages, Categories and Post Tags are automatically indexed and sync'd with Algolia via their API.  
Changes and additions to your content (e.g. new or modified posts) are updated in real time, so your site search is always up-to-date. 
Results are displayed in the standard WordPress search results template, so you have total control over the design. We also automatically add a suggestive search out of the box!


## Installation

If you already have an Algolia account 

1. Download the plugin
2. Activate the plugin
3. Go to the plugin settings and enter the following information: 
  1. APP ID
  2. API Key
  3. API Key for Search Only
4. Save Settings
5. Enter an Algolia Index Name
6. Press “Index content” 
7. Enjoy!


If you don't have an Algolia account yet, just follow the link https://www.algolia.com/


## Setup Maven Algolia and Indexing Content

1. Create an account
If you don’t have an account goes to https://www.algolia.com/users/sign_up and create a new one.


2. Install Maven algolia in WP

3. Read this step if you don't know where the credentials are. 
  1. Log in https://www.algolia.com/users/sign_in 
  2. Goes to “Credentials” section (https://www.algolia.com/licensing)
  3. In credentials section you will see:
    - Application ID
    - API Key: you will need to click in the padlock icon to see it
    - Search-Only API Key: you will need to click in the padlock icon to see it
  
4. Setup your credentials in Maven Algolia Settings
Fill all the fields and click on “Save Changes” button

5. Set the “Index Name”
If all settings are ok you will see a box to set your index name, if you don’t have an index in algolia just set the name you want and the plugin will create the index for you.
Then save the changes.

6. Index Content: After setting your index name you will see a button “Index Content”. Use it to send your site content to algolia.

## Configure your index

![Maven Settings](http://www.sitemavens.com/wp-content/uploads/2014/07/maven-algolia-settings.png)

After indexing all the content you might want to configure your index to offer better results to your visitors. You can do it going to “Indexes” (https://www.algolia.com/explorer) section inside algolia, selecting your index and then going to the “Settings” tab.



## Settings - Customization

Taxonomies
If you want to index taxonomies and show them in search results you will need to enable this feature under “Customization” settings.
It is important to know that if you enable this option you will need to run the indexation to send the taxonomies to algolia.

General
There are some option you have to customize the search results.

- Show Post Categories in search results: it lets you show or hide the post categories in search results. Post categories will be shown separated by “comma (,)”.
Default value is “disabled”

* Show Excerpt in search results: it permits to show or hide the post excerpt in search results.Default value is “disabled”

* Max number of characters to show in excerpt: it permits to set how many characters you want to show in the excerpt. Set it as “0” if you want to show the excerpt entirely.
Default value is “0”

* Show Thumbnails in search results: It permits to show or hide the post thumbnail in search results.
Default value is “disabled”

* Thumbnail Width: it permits to set the width value of the thumbnail in search results.
Default value is “20”

* Thumbnail Height: it permits to set the height value for the thumbnail in search results.
Default value is “40”


## Front end, simple result styles structure

```
<ul class="ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all">
<li class="ui-autocomplete-category">
    <span>Posts</span>
</li>
    <li class="mvn-alg-item ui-menu-item">
   	 <a class="mvn-alg-item-link ui-corner-all" href="{POST_URL}">
   		 <span class="mvn-alg-item-thumbnail"> 
   			 <img width="{SETTINGS_WIDTH}" height="{SETTINGS_HEIGHT}" src="{THUMBNAIL_URL}" class="mvn-alg-item-thumbnail-img">
   		 </span>
   		 <span class="mvn-alg-item-title">{POST_TITLE} <em>{PART_OF_TITLE_FOUND}</em>.</span> 
   		 <span class="mvn-alg-item-excerpt">{POST_EXCERPT}</span> 
   	 </a>
    </li>
<li class="ui-autocomplete-category">
	<span>Categories</span>
</li>
<li class="mvn-alg-cat ui-menu-item">
	<a class="mvn-alg-cat-link ui-corner-all" href="{CATEGORY_URL}">
    	<span class="mvn-alg-cat-title">
        	{CATEGORY_TITLE}<em>{PART_OF_TITLE_FOUND}</em>
    	</span>
	</a>
</li>
</ul>

```

