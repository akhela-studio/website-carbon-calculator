## Website Carbon Calculator

Estimate your web page carbon footprint

### Installation

```shell
$ composer require skild/website-carbon-calculator
```

### API

#### Estimate web page carbon footprint

View the [Google documentation](https://developers.google.com/speed/docs/insights/v5/get-started#APIKey) to generate a Google Pagespeed Api Key

```php
$websiteCarbonCalculator = new WebsiteCarbonCalculator('GooglePagespeedApiKey');
$websiteCarbonCalculator->calculateByURL('https://www.websitecarbon.com/how-does-it-work/')
```

return

```json
{
  "url": "https://www.websitecarbon.com/how-does-it-work",
  "bytesTransferred": 60695,
  "itemsTransferred": 9,
  "isGreenHost": true,
  "performanceScore": 1,
  "loadingExperience": "FAST",
  "energy": 7.703185081481933e-5,
  "co2PerPageview": 0.0331611887928009
}
```

For better performance, detect hosting energy type and store the value in a database.

#### Detect hosting energy type

```php
$isGreenHost = WebsiteCarbonCalculator::isGreenHosting('https://www.websitecarbon.com')
$websiteCarbonCalculator->calculateByURL('https://www.websitecarbon.com/how-does-it-work/', ['isGreenHost'=>$isGreenHost])
```

### How does it work

Calculating the carbon emissions of website is somewhat of a challenge, but using five key pieces of data we can make a pretty good estimate:

- Data transfer over the wire
- Energy intensity of web data
- Energy source used by the data centre
- Carbon intensity of electricity
- Website traffic

Under the hood, it uses [Google pagespeed api](https://developers.google.com/speed/docs/insights/v5/get-started) and [The green web foundation api](https://www.thegreenwebfoundation.org/green-web-check/)

### Website Carbon Calculator

The internet consumes a lot of electricity. 416.2TWh per year to be precise. To give you some perspective, that’s more than the entire United Kingdom.

From data centres to transmission networks to the devices that we hold in our hands, it is all consuming electricity, and in turn producing carbon emissions.

This project is widely inspired by the [Website Carbon Calculator algorithm](https://gitlab.com/wholegrain/carbon-api-2-0)