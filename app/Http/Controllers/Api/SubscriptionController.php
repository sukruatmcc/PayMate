<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Auth::user()->subscriptions;
        return SubscriptionResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount'  => 'required',
            'date'  => 'required|date',
        ]);

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'title'  => $request->title,
            'amount'   => $request->amount,
            'date' => Carbon::parse($request->tarih)->format('Y-m-d')
        ]);

        return response()->json([
            'message' => $request->title . ' abonliğiniz başarıyla eklenmiştir',
            'data' => new SubscriptionResource($subscription)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $subscription = Subscription::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $data = [];

            if ($request->has('title')) {
                $request->validate(['title' => 'required|string|max:255']);
                $data['title'] = $request->title;
            }

            if ($request->has('amount')) {
                $request->validate(['amount' => 'required|numeric']);
                $data['amount'] = $request->amount;
            }

            if ($request->has('date')) {
                $request->validate(['date' => 'required|date']);
                $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
            }

            if (!empty($data)) {
                $subscription->update($data);
            }

            return response()->json([
                'message' => $subscription->title . ' aboneliğiniz başarıyla güncellendi',
                'data'    => new SubscriptionResource($subscription),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Abonelik bulunamadı'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $subscription = Subscription::findOrFail($id);
        $subscription->delete();                     

        return response()->json([
            'message' =>'aboneliğiniz silinmiştir'
        ]);
    }
}
