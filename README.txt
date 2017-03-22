These files are for Canberra Brewers web functionality.

You should not be here unless you are a member of the club who has been given this repository address. Please do not contribute or fork without emailing webmaster@canberrabrewers.com.au.


The Canberra Brewers Club is a not for profit organisation for brewers in the Canberra region. This repository is to allow us a central place to work on custom files for our systems.

The main functions are allowing people to enter club competitions and join Canberra Brewers as members.

*** Membership V1 ***
1. Radio button to select exisitng or new member
New > show main form
Existing > redirect to forum with path to membership form to redirect back after login

2. Main form saves details to membership database including Forum ID the opens PayPal form
New > Create member record
Existing > Update member record

3. Paypal form allows payment and returns with transaction ID to success page
Transaction ID passed: 
- email webmaster, treasurer, president
- email user with how to email
- create a forum user (maybe can't, need to verify user by email??)


*** TODO ***

22/03/2017
Fix forum cookie path to use phpBB for auth
Create DB tables for membership DB
All logic for membership forms according to flow above
Paypal setup return URL in PayPal settings
Paypal check that member details are passed to credit card
Create WP success page with existing Membership Template
Form styles (maybe use WP styles?)

