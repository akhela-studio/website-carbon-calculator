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

Output

```json
{
  "url": "https:\/\/www.websitecarbon.com",
  "isGreenHost": true,
  "bytesTransferred": 135289,
  "networkRequests": 16,
  "performanceScore": 0.94,
  "loadingExperience": "FAST",
  "domSize": 308, //https://web.dev/dom-size/
  "speedIndex": 934, //https://web.dev/speed-index/
  "firstMeaningfulPaint": 491, //https://web.dev/first-meaningful-paint/
  "interactive": 692, //https://web.dev/interactive/
  "bootupTime": 94, //https://web.dev/bootup-time/
  "serverResponseTime": 150, //https://web.dev/time-to-first-byte/
  "mainthreadWork": 762, //https://web.dev/mainthread-work-breakdown/
  "energy": 0.00017170618753880262,
  "co2PerPageview": 0.07391723347728402
}
```

For better performance, detect hosting energy type and store it in a database to avoid repetitive call to The Green Web Foundation.

#### Detect hosting energy type

```php
$isGreenHost = WebsiteCarbonCalculator::isGreenHost('https://www.websitecarbon.com')
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
