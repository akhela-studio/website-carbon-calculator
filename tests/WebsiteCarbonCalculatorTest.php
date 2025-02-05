<?php

use PHPUnit\Framework\TestCase;
use Akhela\WebsiteCarbonCalculator\WebsiteCarbonCalculator;

class WebsiteCarbonCalculatorTest  extends TestCase
{
    public function testIsGreenHost()
    {
        $isGreenHost = WebsiteCarbonCalculator::isGreenHost('https://www.websitecarbon.com');
        $this->assertEquals(true, $isGreenHost);

        $isGreenHost = WebsiteCarbonCalculator::isGreenHost('https://www.apple.com/');
        $this->assertEquals(false, $isGreenHost);
    }

    public function testCalculator()
    {
        $websiteCarbonCalculator = new WebsiteCarbonCalculator(getenv('pagespeedApiKey'));

        $data = $websiteCarbonCalculator->calculateByURL('https://www.websitecarbon.com');

        $is_array = is_array($data);
        $this->assertEquals(true, $is_array);

        $this->assertEquals('https://www.websitecarbon.com', $data['url']);
        $this->assertEquals(true, $data['isGreenHost']);
        $this->assertGreaterThan(100000, $data['bytesTransferred']);
        $this->assertGreaterThan(10, $data['networkRequests']);
        $this->assertGreaterThan(0.9, $data['performanceScore']);
        $this->assertGreaterThan(200, $data['domSize']);
        $this->assertEquals('FAST', $data['loadingExperience']);
        $this->assertGreaterThan(400, $data['speedIndex']);
        $this->assertGreaterThan(200, $data['firstContentfulPaint']);
        $this->assertGreaterThan(200, $data['largestContentfulPaint']);
        $this->assertGreaterThan(5, $data['bootupTime']);
        $this->assertGreaterThan(100, $data['serverResponseTime']);
        $this->assertGreaterThan(100, $data['mainthreadWork']);
        $this->assertGreaterThan(0.00001, $data['energy']);
        $this->assertGreaterThan(0.01, $data['co2PerPageview']);

    }
}
