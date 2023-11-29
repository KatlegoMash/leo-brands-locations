<section class="daily-deal-items card-deck">
    <script>
        trackDailyDealsIconClicks("{!! $category_id ?? "" !!}")
    </script>

@forelse( $dailyDealsItems as $key => $dailyDeal)
    @php
    
        $deal = (object) $dailyDeal;
        $deal->name = \Illuminate\Support\Str::limit($deal->productName, 40);

        if ($isPreview) {
            $trackQueryParams = [
                'url' => $deal->productUrl ?? '',
            ];
        } else {
            $trackQueryParams = [
              'action'        => 'dailydeals',
              '_ul'           => $userLocation ?? '',
              'location'      => $brand_location_id ?? '',
              'url'           => $deal->productUrl ?? '',
              's'             => 0,
              'loctype'       => $locationType ?? '',
              'categoryIds'   => $category_id ?? '',
              'position_icon' => $position_icon ?? '',
              'vicinity_id'   => $vicinity_id ?? '',
              'icon_name'     => 'dailydeals',
              'productName'   => $deal->name,
              'shopName'      => $deal->shopName,
              'productPosition' => $loop->index+1,
            ];
        }

        $clickActionUrlBase = rtrim(
            action('ClickActionController@getClick', [$campaignId, $zoneId]), "/"
        );
        $trackParams = http_build_query($trackQueryParams);

        $deal->url = $clickActionUrlBase . "/?{$trackParams}";
        $deal->url = str_replace('utm_source=PriceCheck', 'utm_source=VicinityMedia',$deal->url);
        $deal->url = str_replace('utm_campaign=PriceCheck', 'utm_campaign=VicinityMedia',$deal->url);
    @endphp

    <div class="card daily-deal-item m-0">
        <div class="card-header container p-0">
            <div class="info-store-discount row no-gutters align-items-center py-0 m-auto">
                @if (property_exists($deal, 'shopName') || property_exists($deal, 'shopLogoUrl'))
                <div class="col-auto logo px-1 py-0">
                    @if (NULL != $deal->shopName && NULL != $deal->shopLogoUrl)
                    <img class="img-fluid p-0"  width="90" height="30" src="{{ $deal->shopLogoUrl }}" alt="Shop - {{ $deal->shopName }}">
                    @else
                        @if (app()->environment('local'))
                        <img class="img-fluid p-0" width="90" height="30" src="https://images.pricecheck.co.za/images/objects/shop/logo_1259.png?1571749092" alt="Shop - Takelot.com">
                        @else
                        <div class="no-shop-logo" style="max-width:90px;width:90px;max-height:30px;height:30px;display:flex;align-self:center;"></div>
                        @endif
                    @endif
                </div>
                @else
                <div class="col-auto logo px-1 py-0">
                    @if (app()->environment('local'))
                    <img class="img-fluid p-0" width="90" height="30" src="https://images.pricecheck.co.za/images/objects/shop/logo_1259.png?1571749092" alt="Shop - Takelot.com">
                    @else
                    <div class="no-shop-logo" style="max-width:90px;width:90px;max-height:30px;height:30px;display:flex;align-self:center;"></div>
                    @endif
                </div>
                @endif
                <div class="col discount px-1 py-0">
                    <div class="price-discount stock-availability">
                        @if($deal->savingPercentage > 0)
                        <span class="d-block">Save: {{ number_format($deal->savingPercentage, 0, ".", "") }}%</span>
                        @else
                        <span class="d-block"></span>
                        @endif
                        @if($deal->stock_count == is_string($deal->stock_count) )
                        <span class="d-block"></span>
                        @else
                        <span class="d-block">{{ $deal->stock_count }} Left</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="image-from-store py-1">
            @if ($deal->imageUrl && $deal->imageUrl != '')
            <img class="card-img-top img-fluid p-0" width="90" height="90" src="{{ $deal->imageUrl }}" alt="Product - {{ $deal->name ?? '' }}">
            @else
            <div class="card-img-top no-image" style="width:90px;height:90px;overflow:hidden;" data-product-name="{{ $deal->name ?? '' }}"></div>
            @endif
        </div>

        <div class="product-title-offer-price card-body text-center p-1">
            <h5 class="card-title my-0">{{ $deal->name ?? '' }}</h5>
            <p class="card-text text-center pricing mt-1 mb-0">
                <span class="current">{{ str_replace(['ZAR', 'USD'], ['R', '$'], $deal->currency) }} {{ $deal->salePrice }}</span>
                @if( $deal->price  != 0)
                was 
                <span class="original strikethrough">{{ str_replace(['ZAR', 'USD'], ['R', '$'], $deal->currency) }} {{ $deal->price }}</span>
                @else
                <span hidden >{{ $deal->price }}</span>
                @endif
            </p>
            <a rel="noopener noreferer" class="product-store-link stretched-link" title="{{ $deal->name }}" href="{{ $deal->url }}" target="_blank" name="daily_deal_{{ $deal->id }}"></a>
        </div>
    </div>
@empty
@endforelse
</section>
