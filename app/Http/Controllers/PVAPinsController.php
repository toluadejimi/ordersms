<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PVAPinsController extends Controller
{
    protected $baseUrl = 'http://api.pvapins.com/user/api/';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.pvapins.key'); // make sure .env has PVAPINS_API_KEY
    }

   public function index(Request $request)
{
    $country = $request->input('country', 'usa');

    $rates = [];
    $response = Http::get($this->baseUrl . 'get_rates.php', [
        'customer' => $this->apiKey,
        'country' => $country,
    ]);

    if ($response->ok()) {
        $rates = $response->json();

        // Optional: validate it's an array
        if (!is_array($rates)) {
            $rates = [];
        }
    }

    return view('pvapins.index', [
        'rates' => $rates,
        'country' => $country,
        'order' => session('order') ?? null,
    ]);
}


    public function purchase(Request $request)
    {
        $request->validate([
            'app' => 'required',
            'country' => 'required',
        ]);

        $response = Http::get($this->baseUrl . 'get_number.php', [
            'customer' => $this->apiKey,
            'app' => $request->app,
            'country' => $request->country,
        ]);

        $result = $response->json();

        return redirect()->route('pvapins.index', [
            'country' => $request->country
        ])->with('order', $result);
    }

    public function checkSms($number, $country, $app)
    {
        $response = Http::get($this->baseUrl . 'get_sms.php', [
            'customer' => $this->apiKey,
            'number' => $number,
            'country' => $country,
            'app' => $app,
        ]);

        return response()->json($response->json());
    }
}
