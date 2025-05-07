# Merchant
This is a school project web application written in PHP, HTML and some basic Javascript. The user can create and manage products, campaigns and advertisements through Google Merchant and Facebook Business with their respective ads managers through this app.

## Installation

1. Clone the repo:
    git clone https://github.com/yourusername/merchant.git

2. Configure environment variables:
    - Copy `.env.example` → `.env`
    - Set `FACEBOOK_APP_ID`, `FACEBOOK_APP_SECRET`, and `PIXEL_ID`
    - Copy `google_ads_php.ini.example` → `google_ads_php.ini`
    - Set `DeveloperToken` from Google Ads, `LoginCustomerId` from a managerAccount in Google Ads, and `ApiVersion` (We used `v19?` in our project)
    - Set `ClientId` from Google Cloud Console, `ClientSecret` from Google Cloud Console, and `RefreshToken` (However it should update automatically once you authenticate yourself with Google OAuth) 

3. Install dependencies using Composer:
    ```bash
    composer install
    ```

4. Start a local server:
    ```bash
    php -S localhost:8000 -t public
    ```
    Or use XAMPP and start up Apache. 
    

5. Navigate to:
    ```
    http://localhost:8000
    ```
    Or if using Xampp. Replace 'Merchant' with another name if you change the folder name.
    ```
    https://127.0.0.1/Merchant/public
    ```