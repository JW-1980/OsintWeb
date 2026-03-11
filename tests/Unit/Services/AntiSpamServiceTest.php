<?php

namespace Tests\Unit\Services;

use App\Models\SpamPattern;
use App\Services\AntiSpamService;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class AntiSpamServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Request|Mockery\MockInterface
     */
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = Mockery::mock(Request::class);
        $this->request->shouldReceive('ip')->andReturn('127.0.0.1');
    }

    /**
     * Test normal content has zero or low score.
     */
    public function test_normal_content_has_low_score()
    {
        $content = "This is a normal comment about the current situation. It is informative and well-written.";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertEqualsWithDelta(0.0, $score['score'], 0.0001);
        $this->assertEmpty($score['flags']);
    }

    /**
     * Test excessive caps heuristic.
     */
    public function test_excessive_caps_detection()
    {
        $content = "THIS IS A VERY LOUD COMMENT THAT USES ALL CAPS TO GET ATTENTION!!!";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertGreaterThan(0, $score['score']);
        $this->assertContains('excessive_caps', array_column($score['flags'], 'type'));
        $this->assertEqualsWithDelta(0.5, $score['score'], 0.0001);
    }

    /**
     * Test excessive caps boundary (length <= 20).
     */
    public function test_caps_not_flagged_for_short_strings()
    {
        $content = "SHORT ALL CAPS"; // 14 chars
        $score = $this->invokeCalculateSpamScore($content);

        $types = array_column($score['flags'], 'type');
        $this->assertNotContains('excessive_caps', $types);
    }

    /**
     * Test excessive links heuristic.
     */
    public function test_excessive_links_detection()
    {
        // 4 links triggers it (count > 3)
        $content = "Check these out: http://link1.com http://link2.com http://link3.com http://link4.com";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertGreaterThan(0, $score['score']);
        $this->assertContains('excessive_links', array_column($score['flags'], 'type'));
        // 4 links = 0.3 * (4-3) = 0.3
        $this->assertEqualsWithDelta(0.3, $score['score'], 0.0001);
    }

    /**
     * Test excessive links exactly at threshold (3 links).
     */
    public function test_excessive_links_at_threshold()
    {
        $content = "Check these out: http://link1.com http://link2.com http://link3.com";
        $score = $this->invokeCalculateSpamScore($content);

        $types = array_column($score['flags'], 'type');
        $this->assertNotContains('excessive_links', $types);
    }

    /**
     * Test excessive punctuation heuristic.
     */
    public function test_excessive_punctuation_detection()
    {
        // Needs > 2 occurrences of [!?]{2,}
        $content = "Is this real?? I can't believe it!! Wow!!!";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertGreaterThan(0, $score['score']);
        $this->assertContains('excessive_punctuation', array_column($score['flags'], 'type'));
        $this->assertEqualsWithDelta(0.2, $score['score'], 0.0001);
    }

    /**
     * Test short content heuristic.
     */
    public function test_too_short_detection()
    {
        $content = "Hi!";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertGreaterThan(0, $score['score']);
        $this->assertContains('too_short', array_column($score['flags'], 'type'));
        $this->assertEqualsWithDelta(0.2, $score['score'], 0.0001);
    }

    /**
     * Test repeated characters heuristic.
     */
    public function test_repeated_characters_detection()
    {
        // 6 or more repeated characters
        $content = "Noooooooo way!";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertGreaterThan(0, $score['score']);
        $this->assertContains('repeated_characters', array_column($score['flags'], 'type'));
        $this->assertEqualsWithDelta(0.3, $score['score'], 0.0001);
    }

    /**
     * Test spam phrases heuristic.
     */
    public function test_spam_phrases_detection()
    {
        $content = "You can make money fast if you click here now!";
        $score = $this->invokeCalculateSpamScore($content);

        $this->assertGreaterThan(0, $score['score']);
        $types = array_column($score['flags'], 'type');
        $this->assertContains('spam_phrase', $types);

        // Two phrases: "make money fast" and "click here"
        // 0.5 + 0.5 = 1.0
        $this->assertEqualsWithDelta(1.0, $score['score'], 0.0001);
    }

    /**
     * Test score capping at 10.0.
     */
    public function test_score_is_capped_at_ten()
    {
        $content = "CLICK HERE BUY NOW LIMITED OFFER ACT NOW FREE MONEY MAKE MONEY FAST WORK FROM HOME CASINO VIAGRA CRYPTOCURRENCY INVESTMENT ";
        $content .= str_repeat("http://link.com ", 30);
        $content .= "!!!!!!!!!!!!";

        $score = $this->invokeCalculateSpamScore($content);

        $this->assertEqualsWithDelta(10.0, $score['score'], 0.0001);
    }

    /**
     * Helper to call protected calculateSpamScore method.
     */
    protected function invokeCalculateSpamScore(string $content): array
    {
        // Mock SpamPattern static calls
        $spamPatternMock = Mockery::mock('alias:App\Models\SpamPattern');
        $spamPatternMock->shouldReceive('active->get')->andReturn(collect([]));

        $service = new class($this->request) extends AntiSpamService {
            public function callCalculateSpamScore(string $content): array
            {
                return $this->calculateSpamScore($content);
            }
        };

        return $service->callCalculateSpamScore($content);
    }
}
