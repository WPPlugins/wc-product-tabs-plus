=== WC Product Tabs Plus ===
Contributors: wooninjas
Tags: tabs, woocommerce, reviews, description, products, woocommerce products, product info
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Advance tab management for WooCommerce Product tabs

== Description ==

WC Product Tabs Plus (WPTP) is an easy to use and intuitive tool to creating, ordering, hiding and managing your own WooCommerce Product tabs. These tabs can contain any content you wish, including shortcodes, and can be Global i.e. displayed on all Products, or specific to just one Product.

= Features: =

* Support for Global Tabs
* Add product specific custom tabs alongside Global tabs on Product edit screen
* Option to hide custom/Global Tabs from a product
* Full WYSIWYG editor with Media upload
* Supports shortcodes
* Sortable (drag/drop ordering)
* Filter Hooks to customize title/content html

Please see the contextual help tabs on admin screen for Global Tab (view/edit screen) and Product screen (edit screen only) for more info.

== Installation ==

Before installation please make sure you have latest WooCommerce installed.

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

= Why are not all of my global tabs being displayed under Product edit screen? =

It's probably because you have cloned/drafted a global tab from the Global Tabs table listing. When you clone, the unique field ID of the tab remains the same and as it is no longer unique it will fail to be listed in the Global Tabs on product edit screen. To make sure a new (unique) Field ID is generated, always use the "Add new" button to create new tabs.

= I have mistakenly deleted my tab. How can I get it back? =

Deletion only happens at the HTML DOM level within the Product backend page i.e. it is a soft delete which does not remove the data from the database until you save the Product. So if you delete one accidentally, you can just refresh the page and have it back.

= Why are some of my tabs not being displayed in the frontend =

The Tab title is a required field for all tabs. If you leave it blank the tab will not be not be displayed in the frontend.

= Can I modify the default WooCommerce tabs, like Reviews, Description? =

No, the plugin only manages it's own Tabs.

== Screenshots ==

1. Global tabs
2. Product tabs under Product Data metabox

== Changelog ==

= 1.0.3 =
* Add filter hook `wptp_tab_object` to override tab object ([Topic#8871911](https://wordpress.org/support/topic/conditional-tabs))