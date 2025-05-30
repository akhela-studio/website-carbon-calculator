## Website Carbon Calculator

The internet consumes a lot of electricity. 1021 TWh per year to be precise. To give you some perspective, that’s more than the entire United Kingdom.

From data centers to transmission networks to the billions of connected devices that we hold in our hands, it is all consuming electricity, and in turn producing carbon emissions equal to or greater than the global aviation industry. Yikes!

The Website Carbon Calculator empowers website owners to assess and minimize their carbon footprint. Drawing inspiration from the acclaimed Website Carbon Calculator algorithm 2.0 and leveraging The Green Web Foundation's co2.js, it allows you to calculate the CO₂ impact and performance of any url.


### Installation

```shell
$ composer require sustainableWeb/website-carbon-calculator
```

### API

#### Estimate web page carbon footprint and performance

View the [Google documentation](https://developers.google.com/speed/docs/insights/v5/get-started#APIKey) to generate a Google Pagespeed Api Key

```php
use SustainableWeb\WebsiteCarbonCalculator\WebsiteCarbonCalculator;

$websiteCarbonCalculator = new WebsiteCarbonCalculator('GooglePagespeedApiKey');
$websiteCarbonCalculator->calculateByURL('https://www.websitecarbon.com/how-does-it-work/', ['strategy'=>'desktop'])
```

Output

```json
{
  "url": "https://www.websitecarbon.com",
  "isGreenHost": true,
  "bytesTransferred": 135289,
  "networkRequests": 16,
  "performanceScore": 0.94,
  "loadingExperience": "FAST",
  "domSize": 308,
  "speedIndex": 934,
  "firstMeaningfulPaint": 491,
  "interactive": 692, 
  "bootupTime": 94,
  "serverResponseTime": 150,
  "mainthreadWork": 762,
  "energy": 0.000171,
  "co2PerPageview": 0.0739
}
```

For better performance, detect hosting energy type and store it in a database to avoid repetitive call to The Green Web Foundation API.

#### Detect hosting energy type

```php
use SustainableWeb\WebsiteCarbonCalculator\WebsiteCarbonCalculator;

$isGreenHost = WebsiteCarbonCalculator::isGreenHost('https://www.websitecarbon.com')
$websiteCarbonCalculator->calculateByURL('https://www.websitecarbon.com/how-does-it-work/', ['isGreenHost'=>$isGreenHost, 'strategy'=>'desktop'])
```

### How does it work

Calculating the carbon emissions of website is somewhat of a challenge, but using five key pieces of data we can make a pretty good estimate:

- Data transfer over the wire
- Energy intensity of web data
- Energy source used by the data center
- Carbon intensity of electricity
- Website traffic

Under the hood, it uses [Google pagespeed api](https://developers.google.com/speed/docs/insights/v5/get-started) and [The green web foundation api](https://www.thegreenwebfoundation.org/green-web-check/)

### Website Carbon Calculator

This project is widely inspired by the [Website Carbon Calculator algorithm 2.0](https://gitlab.com/wholegrain/carbon-api-2-0) and [The Green Web Foundation co2.js](https://github.com/thegreenwebfoundation/co2.js)

### Glossary

- strategy : mobile/desktop
- isGreenHost : server uses sustainable energy
- networkRequests : https://web.dev/performance-scoring/
- performanceScore : https://web.dev/resource-summary/
- domSize : https://web.dev/dom-size/
- speedIndex : https://web.dev/speed-index/
- firstContentfulPaint : https://web.dev/articles/fcp
- largestContentfulPaint : https://web.dev/articles/lcp
- interactive : https://web.dev/interactive/
- bootupTime : https://web.dev/bootup-time/
- serverResponseTime : https://web.dev/time-to-first-byte/
- mainthreadWork : https://web.dev/mainthread-work-breakdown/
- co2PerPageview : estimated grams of CO2 produced every time someone visits the web page

### Tests

Run tests 
```shell
$ pagespeedApiKey=xxxxxx php vendor/bin/phpunit
```
