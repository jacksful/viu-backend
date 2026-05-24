<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\UploadedZipcode;
use App\Models\UserZipcodeSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
  /**
   * Display the customer dashboard
   */
  public function index(Request $request)
  {
    $user = Auth::user();

    // Get user's subscribed zipcode IDs
    $subscriptions = UserZipcodeSubscription::where('user_id', $user->id)
      ->where('status', 'active')
      ->get();

    $zipcodeIds = collect();
    foreach ($subscriptions as $subscription) {
      $zipcodeIds = $zipcodeIds->merge($subscription->zipcode_ids ?? []);
    }
    $zipcodeIds = $zipcodeIds->unique()->values()->all();

    // Base query for datasets
    $query = Dataset::with(['uploadedZipcode.zipcode'])
      ->whereHas('uploadedZipcode', function ($q) use ($zipcodeIds) {
        $q->whereIn('zipcode_id', $zipcodeIds);
      });

    // Apply filters
    if ($request->filled('zipcode')) {
      $query->whereHas('uploadedZipcode.zipcode', function ($q) use ($request) {
        $q->where('code', $request->zipcode);
      });
    }

    if ($request->filled('month') && $request->filled('year')) {
      $query->whereHas('uploadedZipcode', function ($q) use ($request) {
        $q->where('month', $request->month)
          ->where('year', $request->year);
      });
    }

    if ($request->filled('predicted_status') && $request->predicted_status !== 'all') {
      $query->where('predicted_status', $request->predicted_status);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('propertyid', 'like', "%{$search}%")
          ->orWhere('address', 'like', "%{$search}%");
      });
    }

    // Get total count before pagination
    $totalProperties = $query->count();

    // Calculate metrics
    $totalDatasets = Dataset::whereHas('uploadedZipcode', function ($q) use ($zipcodeIds) {
      $q->whereIn('zipcode_id', $zipcodeIds);
    })->count();

    $correctPredictions = Dataset::whereHas('uploadedZipcode', function ($q) use ($zipcodeIds) {
      $q->whereIn('zipcode_id', $zipcodeIds);
    })->where('correct_status', 'Yes')->count();

    $averageAccuracy = $totalDatasets > 0
      ? round(($correctPredictions / $totalDatasets) * 100, 1)
      : 0;

    $averageProbability = Dataset::whereHas('uploadedZipcode', function ($q) use ($zipcodeIds) {
      $q->whereIn('zipcode_id', $zipcodeIds);
    })->whereNotNull('status_probability')
      ->avg('status_probability');

    $predictiveScore = $averageProbability ? round($averageProbability * 100, 1) : 0;

    // Paginate results
    $perPage = $request->get('per_page', 25);
    $datasets = $query->orderBy('propertyid')
      ->paginate($perPage)
      ->withQueryString();

    // Get available zipcodes for filter
    $availableZipcodes = \App\Models\Zipcode::whereIn('id', $zipcodeIds)
      ->orderBy('code')
      ->get()
      ->map(function ($zipcode) {
        return [
          'code' => $zipcode->code,
          'label' => $zipcode->code . ' - ' . ($zipcode->city ?? '') . ', ' . ($zipcode->state ?? ''),
        ];
      });

    // Get available months/years
    $availableMonths = UploadedZipcode::whereIn('zipcode_id', $zipcodeIds)
      ->select('month', 'year')
      ->distinct()
      ->orderBy('year', 'desc')
      ->orderBy('month', 'desc')
      ->get()
      ->map(function ($uploaded) {
        $months = [
          1 => 'January',
          2 => 'February',
          3 => 'March',
          4 => 'April',
          5 => 'May',
          6 => 'June',
          7 => 'July',
          8 => 'August',
          9 => 'September',
          10 => 'October',
          11 => 'November',
          12 => 'December',
        ];
        return [
          'month' => $uploaded->month,
          'year' => $uploaded->year,
          'label' => ($months[$uploaded->month] ?? $uploaded->month) . ' ' . $uploaded->year,
        ];
      })
      ->unique('label')
      ->values();

    // Get user profile data for dropdown
    $activeSubscriptions = UserZipcodeSubscription::where('user_id', $user->id)
      ->where('status', 'active')
      ->get();

    $assignedZipcodes = collect();
    foreach ($activeSubscriptions as $subscription) {
      $zipcodes = \App\Models\Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
      $assignedZipcodes = $assignedZipcodes->merge($zipcodes);
    }
    $assignedZipcodes = $assignedZipcodes->unique('id')->values();

    // Determine plan based on subscriptions (Professional if has active subscriptions)
    $plan = $activeSubscriptions->isNotEmpty() ? 'Professional' : 'Free';

    // Format member since date
    $memberSince = $user->created_at->format('F Y');

    return view('customer.dashboard', compact(
      'datasets',
      'totalProperties',
      'averageAccuracy',
      'predictiveScore',
      'availableZipcodes',
      'availableMonths',
      'assignedZipcodes',
      'plan',
      'memberSince'
    ));
  }

  /**
   * Export datasets to CSV
   */
  public function export(Request $request)
  {
    $user = Auth::user();

    // Get user's subscribed zipcode IDs
    $subscriptions = UserZipcodeSubscription::where('user_id', $user->id)
      ->where('status', 'active')
      ->get();

    $zipcodeIds = collect();
    foreach ($subscriptions as $subscription) {
      $zipcodeIds = $zipcodeIds->merge($subscription->zipcode_ids ?? []);
    }
    $zipcodeIds = $zipcodeIds->unique()->values()->all();

    $query = Dataset::with(['uploadedZipcode.zipcode'])
      ->whereHas('uploadedZipcode', function ($q) use ($zipcodeIds) {
        $q->whereIn('zipcode_id', $zipcodeIds);
      });

    // Apply same filters as dashboard
    if ($request->filled('zipcode')) {
      $query->whereHas('uploadedZipcode.zipcode', function ($q) use ($request) {
        $q->where('code', $request->zipcode);
      });
    }

    if ($request->filled('month') && $request->filled('year')) {
      $query->whereHas('uploadedZipcode', function ($q) use ($request) {
        $q->where('month', $request->month)
          ->where('year', $request->year);
      });
    }

    if ($request->filled('predicted_status') && $request->predicted_status !== 'all') {
      $query->where('predicted_status', $request->predicted_status);
    }

    if ($request->filled('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('propertyid', 'like', "%{$search}%")
          ->orWhere('address', 'like', "%{$search}%");
      });
    }

    $datasets = $query->orderBy('propertyid')->get();

    $filename = 'property-dataset-' . date('Y-m-d-His') . '.csv';

    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use ($datasets) {
      $file = fopen('php://output', 'w');

      // CSV Headers
      fputcsv($file, [
        'Zipcode',
        'Property ID',
        'Address',
        'Type',
        'Tax Value',
        'Times Sold',
        'Days Since Sold',
        'Last Date Sold',
        'Township',
        'Style',
        'Year Built',
        'Exterior Wall Finish',
        'Roof Type',
        'Roof Material',
        'Basement',
        'HC Type',
        'HC Fuel Type',
        'HC System Type',
        'Bedrooms',
        'Full Baths',
        'SFLA',
        'Physical Condition',
        'Utility',
        'Property Desirability',
        'Location Desirability',
        'Status',
        'Predicted Status',
        'Status Probability',
        'Correct Status',
      ]);

      // CSV Data
      foreach ($datasets as $dataset) {
        fputcsv($file, [
          $dataset->uploadedZipcode->zipcode->code ?? '',
          $dataset->propertyid ?? '',
          $dataset->address ?? '',
          $dataset->restype ?? '',
          $dataset->tax_value ?? '',
          $dataset->times_sold ?? '',
          $dataset->day_since_sold ?? '',
          $dataset->last_date_sold ?? '',
          $dataset->township ?? '',
          $dataset->style ?? '',
          $dataset->yearbuilt ?? '',
          $dataset->extwallfinish_desc ?? '',
          $dataset->rooftype_desc ?? '',
          $dataset->roofmaterial_desc ?? '',
          $dataset->basement_desc ?? '',
          $dataset->hctype ?? '',
          $dataset->hcfueltype_desc ?? '',
          $dataset->hcsystemtype_desc ?? '',
          $dataset->bedrooms ?? '',
          $dataset->fullbaths ?? '',
          $dataset->sfla ?? '',
          $dataset->phycondition ?? '',
          $dataset->utility ?? '',
          $dataset->propdesirability ?? '',
          $dataset->locdesirability ?? '',
          $dataset->status ?? '',
          $dataset->predicted_status ?? '',
          $dataset->status_probability ? round($dataset->status_probability * 100, 1) . '%' : '',
          $dataset->correct_status === 'Yes' ? 'Yes' : 'No',
        ]);
      }

      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }
}
