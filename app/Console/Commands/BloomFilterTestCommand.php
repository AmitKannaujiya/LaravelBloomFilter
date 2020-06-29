<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Averias\RedisBloom\Factory\RedisBloomFactory;
use Averias\RedisBloom\Enum\Connection;
use Averias\RedisBloom\Enum\OptionalParams;
use Faker\Generator as Faker;

class BloomFilterTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmd:bloom-filter-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will test bloom filter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    const SCAN_KEY = 'scan-key';
    const TARGET_SCAN_KEY = 'target-scan-key';

    protected $SAMPLE_DATA = 'www.sampleurl';

    const CAPACITY = 10000;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Faker $faker)
    {

        $factory = new RedisBloomFactory([Connection::DATABASE => 15]);

        $client = $factory->createClient();


        $insertData = [];

        for ($index = 0; $index < self::CAPACITY; $index++) {
            $insertData[] = "{$this->SAMPLE_DATA}-{$index}.com";
        }
        $client->bloomFilterInsert(
            self::SCAN_KEY,
            $insertData,
            [OptionalParams::CAPACITY => self::CAPACITY, OptionalParams::ERROR => 0.3]
        );

        $bloomFilter = $factory->createBloomFilter(self::SCAN_KEY);
        $bloomFilter->copy(self::TARGET_SCAN_KEY);

        $multiExists = $client->bloomFilterMultiExists(self::TARGET_SCAN_KEY, ...$insertData);

        $nonExistentKey = [];
        foreach ($multiExists as $key => $exist) {
            if ($exist === false) {
                $nonExistentKey[] = $insertData[$key];
            }
        }

        if (empty($nonExistentKey)) {
            $this->info('all items were copied');
        } else {
            $this->info(count($nonExistentKey) . " were not copied:");
            var_dump($nonExistentKey);
        }
        //$this->testBloomFilter($faker, $bloomFilter);
        $this->randomTestBloomFilter($faker, $bloomFilter);

        $client->del(self::SCAN_KEY);
        $client->del(self::TARGET_SCAN_KEY);
    }

    /**
     * This will print test result
     * 
     */
    private function printTestResult($value, $message, $all = 0)
    {
        $result = $value ? 'OK!' : 'FAILED!';
        if ($all == 0) {
            $value ? $this->info(sprintf("%s: %s", $message, $result)) : $this->warn(sprintf("%s: %s", $message, $result));
        } else if ($all == 1 && $value) {
            $this->info(sprintf("%s: %s", $message, $result));
        } else if ($all == -1 && !$value) {
            $this->warn(sprintf("%s: %s", $message, $result));
        }
    }

    /**
     * Generate random test cases for the bloom filters 
     */
    private function randomTestBloomFilter(Faker $faker, $bloomFilter)
    {
        $this->info('*** Bloom Filter Test ***');
        $noOfTestCases = $faker->numberBetween(50, 100);
        for ($index = 0; $index < $noOfTestCases; $index++) {
            $randomNo = $faker->numberBetween(-10,  self::CAPACITY + 10);
            $url = "{$this->SAMPLE_DATA}-{$randomNo}.com";
            $this->printTestResult($bloomFilter->exists($url), "exists $url ");
        }
    }

    /**
     * Generate test cases for the bloom filters 
     */
    private function testBloomFilter(Faker $faker, $bloomFilter)
    {
        $this->info('*** Bloom Filter Test ***');
        for ($index = 0; $index < self::CAPACITY; $index++) {
            $url = "{$this->SAMPLE_DATA}-{$index}.com";
            $this->printTestResult($bloomFilter->exists($url), "exists $url ", -1);
        }
    }
}
