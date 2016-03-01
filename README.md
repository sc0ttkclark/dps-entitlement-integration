Adobe DPS Direct Entitlement Integration for WordPress
=============

This plugin integrates the [Adobe DPS Direct Entitlement](http://www.adobe.com/devnet/digitalpublishingsuite/articles/direct-entitlement-starter-kit.html) Server XML endpoints with WordPress. WordPress Users are extended for this functionality to allow for Adobe DPS Entitlements.

## Requirements

* WordPress 4.4+
* PHP 5.5+
* SimpleXML PHP extension enabled

## Installation

1. Activate plugin in WordPress
2. In WordPress, set your Fulfillment Account ID at Settings > DPS Entitlement Server, see *Getting your Adobe DPS Fulfillment Account ID* section below
3. In DPS App Builder, set your configuration according to the *DPS App Builder instructions* section below

## Endpoints

### Adobe DPS Direct Entitlement Server

* **/SignInWithCredentials** `{https://yourwpsite.com}/dps-api/SignInWithCredentials` -- Used to pass in credentials to log a user in  
* **/verifyEntitlement** `{https://yourwpsite.com}/dps-api/verifyEntitlement` -- Used to verify user has access to a specific entitlement
* **/RenewAuthToken** `{https://yourwpsite.com}/dps-api/RenewAuthToken` -- Used to check/renew the authentication of an ongoing device session
* **/entitlements** `{https://yourwpsite.com}/dps-api/entitlements` -- Used to get a list of all entitlements a user has access to

**Note:** *The `/dps-api/` is at the root of your WordPress site, which may be in a subdirectory or a different path depending on where it's installed and if you are using WordPress Multisite.*

## Getting your Adobe DPS Fulfillment Account ID

* Get it from the [DPS Entitlement App lookup](https://www.dpsapps.com/dps/entitlement/index.php) (may not be correct GUID if you don't enter the primary DPS Account with 'Application Role' - DPS App Builder shows this account as 'Title ID')
* Or get GUID from your Adobe DPS app file: AppName.ipa > Archive Utility > AppName/Payload/viewer.app > Show Package Contents > LibraryConfig.plist > /serviceOptions/fulfillment/loginOptions/accountId/value

*[Additional information about getting your Adobe DPS Fulfillment Account ID](http://www.adobe.com/devnet/digitalpublishingsuite/articles/dps-custom-store.html#articlecontentAdobe_numberedheader_2)*

## DPS App Builder instructions

When in the [DPS App Builder](https://helpx.adobe.com/digital-publishing-suite/help/dps-app-builder.html), you will need to use details from below to integrate with your WordPress site.  

**Note:** *Using an SSL certificate on your site is **STRONGLY RECOMMENDED***

* **Viewer Type:** You can use *Multi-issue with Entitlement* OR *Multi-issue with Entitlement and iTunes Subscription*
* **Service URL:** `{https://yourwpsite.com}/dps-api/`
* **Service Auth URL:** `{https://yourwpsite.com}/dps-api/`
* **Integrator ID:** You'll need to get this from an Adobe representative. For testing, this can be something like "WordPress DPS Integration". You should contact your Adobe DPS representative and [fill out this form and e-mail it to them](http://download.macromedia.com/pub/developer/dps/adobe_dps_direct_entitlement_request_form.pdf).
* **Optional Create Account URL:** `{https://yourwpsite.com}/wp-login.php?action=register` or a custom registration page **Note:** *Avoid using a form that has Login because that can cause confusion since it doesn't log them into the app* -- This isn't build into the plugin.
* **Forgot Password URL:** `{https://yourwpsite.com}/wp-login.php?action=lostpassword` or a custom forgot password page **Note:** *Avoid using a form that has Login because that can cause confusion since it doesn't log them into the app* -- This is not currently built into the plugin.
* **Optional Existing Subscription URL:** If you want, you can use a custom existing subscription page to allow allow print subscribers to create their digital account by providing their print subscription number. -- This is not currently built into the plugin.
* **Banner Page URL:** If you want, you can use a custom page with your logo banner. -- This is not currently built into the plugin.
* **Offline Banner Assets:** You'll need Explain how to make Offline Banner Assets

Of course you'll need to get your certificates and provisioning profile in order to test and publish your app.

*[Additional information about building with the Adobe DPS App Builder](https://helpx.adobe.com/digital-publishing-suite/help/create-custom-viewer-app-ipad.html)*

*[Additional information about Direct Entitlements with Adobe DPS App Builder](http://www.adobe.com/devnet/digitalpublishingsuite/articles/direct-entitlement-starter-kit.html#articlecontentAdobe_text_1)*

## See also

@StudioMercury has also built a few plugins that may be useful to those using Adobe DPS.

* https://wordpress.org/plugins/digital-publishing/ (also https://github.com/StudioMercury/digital-publishing-tools-for-wordpress)
* Another recent one from them not yet on WordPress.org, but can be found here: https://github.com/StudioMercury/folio-author-plugin-for-wordpress