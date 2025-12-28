---
name: sales
description: Sales expertise covering CRM, lead management, pipeline tracking, quotations, invoicing workflows, and Dutch B2B/B2C sales practices
version: 1.0.2
tags: [sales, crm, leads, pipeline, quotes, invoicing, dutch-sales, b2b, b2c]
trigger_keywords: [sk-sales, sales, crm, leads, pipeline, quotes, quotations, "sales expert", sales-expert]
---
# Sales Expert Skill

Comprehensive Sales expertise for the Boekhouder application, covering the full sales cycle from lead generation to invoice collection, CRM management, and Dutch B2B/B2C sales practices.

## When to Use This Skill

- Managing sales pipelines and opportunities
- Creating and sending quotations
- Converting quotes to invoices
- Lead management and qualification
- Customer relationship management
- Sales reporting and analytics
- Commission calculations
- Dutch sales tax and invoicing requirements

## Quick Reference

### Sales Cycle Overview

```markdown
Lead → Qualified → Opportunity → Proposal → Negotiation → Won/Lost → Invoice → Collected
```

### Pipeline Stages

| Stage | Probability | Actions | KPIs |
|-------|-------------|---------|------|
| Lead | 10% | Qualify, research | Volume, source |
| Qualified | 25% | Needs analysis | Conversion rate |
| Opportunity | 50% | Solution design | Avg deal size |
| Proposal | 75% | Quote sent | Response time |
| Negotiation | 90% | Terms discussion | Discount rate |
| Won | 100% | Convert to invoice | Win rate |
| Lost | 0% | Post-mortem | Loss reasons |

## Lead Management

### Lead Qualification (BANT)

```php
// BANT qualification criteria
class LeadQualification
{
    public function qualify(Lead $lead): QualificationResult
    {
        $score = 0;
        $criteria = [];

        // Budget - Do they have budget?
        if ($lead->estimated_budget >= $this->minimumDealSize) {
            $score += 25;
            $criteria['budget'] = true;
        }

        // Authority - Decision maker?
        if ($lead->contact_role === 'decision_maker' || $lead->contact_role === 'c_level') {
            $score += 25;
            $criteria['authority'] = true;
        }

        // Need - Clear business need?
        if ($lead->pain_points && count($lead->pain_points) > 0) {
            $score += 25;
            $criteria['need'] = true;
        }

        // Timeline - When do they want to buy?
        if ($lead->expected_close_date && $lead->expected_close_date <= now()->addMonths(3)) {
            $score += 25;
            $criteria['timeline'] = true;
        }

        return new QualificationResult($score, $criteria);
    }
}
```

### Lead Sources

```php
// Track lead sources for ROI analysis
enum LeadSource: string
{
    case WEBSITE = 'website';
    case REFERRAL = 'referral';
    case COLD_CALL = 'cold_call';
    case TRADE_SHOW = 'trade_show';
    case SOCIAL_MEDIA = 'social_media';
    case ADVERTISEMENT = 'advertisement';
    case KVK_SEARCH = 'kvk_search';  // Dutch Chamber of Commerce
    case PARTNER = 'partner';
    case EXISTING_CLIENT = 'existing_client';
}

class LeadTracking
{
    public function attributeSource(Lead $lead): void
    {
        // Track UTM parameters
        $lead->utm_source = request()->input('utm_source');
        $lead->utm_medium = request()->input('utm_medium');
        $lead->utm_campaign = request()->input('utm_campaign');

        // Calculate acquisition cost
        $lead->acquisition_cost = $this->calculateCAC($lead->source);
    }
}
```

## CRM Features

### Contact Management

```php
// Contact and company relationship
class Contact extends Model
{
    protected $fillable = [
        'company_id',      // Tenant isolation
        'client_id',       // Optional link to client
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'department',
        'is_primary',      // Primary contact for client
        'is_billing',      // Billing contact
        'is_decision_maker',
        'linkedin_url',
        'notes',
        'tags',
        'last_contacted_at',
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class)->orderBy('created_at', 'desc');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
```

### Activity Tracking

```php
// Log all sales activities
class Activity extends Model
{
    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'meeting';
    const TYPE_DEMO = 'demo';
    const TYPE_NOTE = 'note';
    const TYPE_TASK = 'task';

    protected $fillable = [
        'company_id',
        'contact_id',
        'opportunity_id',
        'user_id',  // Sales rep
        'type',
        'subject',
        'description',
        'outcome',
        'duration_minutes',
        'scheduled_at',
        'completed_at',
        'follow_up_date',
    ];
}
```

## Opportunity Management

### Pipeline Tracking

```php
class Opportunity extends Model
{
    use BelongsToCompany;

    const STAGE_LEAD = 'lead';
    const STAGE_QUALIFIED = 'qualified';
    const STAGE_OPPORTUNITY = 'opportunity';
    const STAGE_PROPOSAL = 'proposal';
    const STAGE_NEGOTIATION = 'negotiation';
    const STAGE_WON = 'won';
    const STAGE_LOST = 'lost';

    protected $fillable = [
        'company_id',
        'client_id',
        'contact_id',
        'assigned_to',  // Sales rep
        'name',
        'description',
        'stage',
        'probability',
        'amount',
        'currency',
        'expected_close_date',
        'actual_close_date',
        'lost_reason',
        'competitor',
        'source',
    ];

    public function getWeightedValue(): float
    {
        return $this->amount * ($this->probability / 100);
    }

    public function advanceStage(): void
    {
        $stages = [
            self::STAGE_LEAD => self::STAGE_QUALIFIED,
            self::STAGE_QUALIFIED => self::STAGE_OPPORTUNITY,
            self::STAGE_OPPORTUNITY => self::STAGE_PROPOSAL,
            self::STAGE_PROPOSAL => self::STAGE_NEGOTIATION,
            self::STAGE_NEGOTIATION => self::STAGE_WON,
        ];

        if (isset($stages[$this->stage])) {
            $this->update([
                'stage' => $stages[$this->stage],
                'probability' => $this->getDefaultProbability($stages[$this->stage]),
            ]);

            event(new OpportunityStageChanged($this));
        }
    }
}
```

### Forecasting

```php
class SalesForecast
{
    public function generateForecast(Company $company, Carbon $period): ForecastData
    {
        $opportunities = Opportunity::where('company_id', $company->id)
            ->whereIn('stage', ['opportunity', 'proposal', 'negotiation'])
            ->whereMonth('expected_close_date', $period->month)
            ->get();

        return new ForecastData([
            'best_case' => $opportunities->sum('amount'),
            'weighted' => $opportunities->sum(fn($o) => $o->getWeightedValue()),
            'committed' => $opportunities
                ->where('probability', '>=', 90)
                ->sum('amount'),
            'pipeline_value' => $opportunities->count(),
            'by_stage' => $opportunities->groupBy('stage')
                ->map(fn($group) => $group->sum('amount')),
        ]);
    }
}
```

## Quotation Management

### Quote Generation

```php
class Quote extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'client_id',
        'opportunity_id',
        'quote_number',
        'status',  // draft, sent, accepted, rejected, expired
        'valid_until',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'vat_amount',
        'total',
        'currency',
        'notes',
        'terms_and_conditions',
        'pdf_path',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
    ];

    public function lines()
    {
        return $this->hasMany(QuoteLine::class);
    }

    public function convertToInvoice(): Invoice
    {
        DB::transaction(function () {
            $invoice = Invoice::create([
                'company_id' => $this->company_id,
                'client_id' => $this->client_id,
                'quote_id' => $this->id,
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discount_amount,
                'vat_amount' => $this->vat_amount,
                'total' => $this->total,
                'currency' => $this->currency,
                'notes' => $this->notes,
            ]);

            foreach ($this->lines as $line) {
                $invoice->lines()->create($line->toArray());
            }

            // Update opportunity if linked
            if ($this->opportunity) {
                $this->opportunity->update([
                    'stage' => 'won',
                    'probability' => 100,
                    'actual_close_date' => now(),
                ]);
            }

            return $invoice;
        });
    }
}
```

### Dutch Quote Requirements

```markdown
## Dutch Quote Requirements

### Mandatory Elements
- Company name and address
- KvK number
- BTW number
- Bank account (IBAN)
- Quote number and date
- Client details
- Item descriptions
- Unit prices excl. BTW
- BTW percentage per item
- Subtotal, BTW, Total
- Validity period
- Terms and conditions

### Best Practices
- Clear payment terms
- Delivery conditions
- Acceptance procedure
- Change request process
- Cancellation policy
```

## Sales Reporting

### Key Performance Indicators

```php
class SalesKPIs
{
    public function calculate(Company $company, Carbon $period): array
    {
        $opportunities = Opportunity::where('company_id', $company->id);
        $won = (clone $opportunities)->where('stage', 'won')->whereMonth('actual_close_date', $period->month);
        $lost = (clone $opportunities)->where('stage', 'lost')->whereMonth('actual_close_date', $period->month);

        return [
            // Revenue metrics
            'revenue' => $won->sum('amount'),
            'avg_deal_size' => $won->avg('amount'),
            'largest_deal' => $won->max('amount'),

            // Activity metrics
            'win_rate' => $won->count() / ($won->count() + $lost->count()) * 100,
            'avg_sales_cycle' => $this->calculateAvgSalesCycle($won->get()),

            // Pipeline metrics
            'pipeline_value' => $opportunities
                ->whereNotIn('stage', ['won', 'lost'])
                ->sum('amount'),
            'weighted_pipeline' => $opportunities
                ->whereNotIn('stage', ['won', 'lost'])
                ->get()
                ->sum(fn($o) => $o->getWeightedValue()),

            // Efficiency metrics
            'leads_this_month' => Lead::where('company_id', $company->id)
                ->whereMonth('created_at', $period->month)->count(),
            'conversion_rate' => $this->calculateConversionRate($company, $period),
            'quotes_sent' => Quote::where('company_id', $company->id)
                ->whereMonth('sent_at', $period->month)->count(),
            'quote_acceptance_rate' => $this->calculateQuoteAcceptance($company, $period),
        ];
    }
}
```

### Sales Reports

```markdown
## Standard Sales Reports

### Pipeline Report
- Opportunities by stage
- Weighted pipeline value
- Expected close dates
- Aging analysis

### Activity Report
- Calls, emails, meetings per rep
- Conversion by activity type
- Response times
- Follow-up compliance

### Revenue Report
- Won deals by period
- Revenue by product/service
- Revenue by client segment
- Year-over-year comparison

### Forecast Report
- Expected revenue by month
- Best case vs weighted vs committed
- Pipeline coverage ratio
- Forecast accuracy
```

## Commission Management

### Commission Calculation

```php
class CommissionCalculator
{
    public function calculate(User $salesRep, Carbon $period): CommissionResult
    {
        $commissionRules = $salesRep->commissionRules;
        $deals = Opportunity::where('assigned_to', $salesRep->id)
            ->where('stage', 'won')
            ->whereMonth('actual_close_date', $period->month)
            ->get();

        $totalRevenue = $deals->sum('amount');
        $totalCommission = 0;

        // Tiered commission structure
        foreach ($commissionRules->tiers as $tier) {
            if ($totalRevenue >= $tier['threshold']) {
                $tierAmount = min($totalRevenue - $tier['threshold'], $tier['cap'] ?? PHP_INT_MAX);
                $totalCommission += $tierAmount * ($tier['rate'] / 100);
            }
        }

        // Product-specific bonuses
        foreach ($deals as $deal) {
            if ($commissionRules->productBonuses[$deal->product_id] ?? false) {
                $totalCommission += $deal->amount * ($commissionRules->productBonuses[$deal->product_id] / 100);
            }
        }

        return new CommissionResult([
            'sales_rep_id' => $salesRep->id,
            'period' => $period,
            'total_revenue' => $totalRevenue,
            'deal_count' => $deals->count(),
            'commission_amount' => $totalCommission,
            'breakdown' => $this->getBreakdown($deals, $commissionRules),
        ]);
    }
}
```

## Dutch B2B Sales Practices

### KvK Integration

```php
// Use KvK API for lead enrichment
class KvKLeadEnrichment
{
    public function enrichLead(Lead $lead): void
    {
        if ($lead->kvk_number) {
            $kvkData = $this->kvkApi->getCompany($lead->kvk_number);

            $lead->update([
                'company_name' => $kvkData['naam'],
                'address' => $kvkData['adres'],
                'postal_code' => $kvkData['postcode'],
                'city' => $kvkData['plaats'],
                'sbi_codes' => $kvkData['sbiActiviteiten'],
                'employee_count' => $kvkData['werkzamePersonen'],
                'founding_date' => $kvkData['datumOprichting'],
            ]);
        }
    }
}
```

### BTW Handling in Sales

```php
// VAT handling for quotes and invoices
class VATCalculator
{
    public function calculateForSale(Quote $quote): VATResult
    {
        $client = $quote->client;

        // Domestic sale
        if ($client->country === 'NL') {
            return $this->applyDutchVAT($quote);
        }

        // EU B2B - Reverse charge (ICL)
        if ($client->isEUBusiness() && $client->vat_number) {
            return new VATResult([
                'rate' => 0,
                'type' => 'reverse_charge',
                'note' => 'BTW verlegd',
            ]);
        }

        // EU B2C - Destination VAT (OSS if applicable)
        if ($client->isEU() && !$client->vat_number) {
            return $this->applyDestinationVAT($quote, $client->country);
        }

        // Non-EU - No VAT
        return new VATResult([
            'rate' => 0,
            'type' => 'export',
            'note' => 'Export buiten EU',
        ]);
    }
}
```

## Email Templates

### Sales Email Templates

```php
// Automated sales emails
class SalesEmailTemplates
{
    public function getQuoteEmail(Quote $quote): array
    {
        return [
            'subject' => "Offerte {$quote->quote_number} - {$quote->company->name}",
            'template' => 'emails.sales.quote',
            'data' => [
                'quote' => $quote,
                'contact' => $quote->client->primaryContact,
                'valid_until' => $quote->valid_until->format('d-m-Y'),
                'accept_url' => route('quotes.accept', $quote->token),
            ],
        ];
    }

    public function getFollowUpEmail(Quote $quote): array
    {
        return [
            'subject' => "Opvolging offerte {$quote->quote_number}",
            'template' => 'emails.sales.follow_up',
            'data' => [
                'quote' => $quote,
                'days_pending' => $quote->sent_at->diffInDays(now()),
            ],
        ];
    }
}
```

## Automation Rules

### Sales Automation

```php
// Automated sales workflow triggers
class SalesAutomation
{
    public function registerTriggers(): void
    {
        // Lead scoring update
        Event::listen(LeadActivityRecorded::class, function ($event) {
            $this->updateLeadScore($event->lead);
        });

        // Quote follow-up
        Event::listen(QuoteSent::class, function ($event) {
            $this->scheduleFollowUp($event->quote, days: 3);
        });

        // Quote expiring soon
        Event::listen(QuoteExpiring::class, function ($event) {
            $this->sendExpirationReminder($event->quote);
        });

        // Opportunity stale alert
        Event::listen(OpportunityStale::class, function ($event) {
            $this->alertSalesRep($event->opportunity);
        });

        // Win notification
        Event::listen(OpportunityWon::class, function ($event) {
            $this->celebrateWin($event->opportunity);
            $this->triggerOnboarding($event->opportunity);
        });
    }
}
```

## Troubleshooting

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| Quote not syncing to mobile | Offline mode active | Check sync status |
| Pipeline totals incorrect | Currency mismatch | Standardize to EUR |
| Commission calculation off | Tier thresholds | Verify commission rules |
| Lead duplicate | No dedup rules | Enable duplicate checking |
| Quote PDF generation fails | Template error | Check Blade template |

### Quote Troubleshooting

```php
// Debug quote generation
class QuoteDebugger
{
    public function diagnose(Quote $quote): array
    {
        return [
            'has_lines' => $quote->lines()->count() > 0,
            'client_valid' => $quote->client && $quote->client->exists,
            'calculations_valid' => abs(
                $quote->lines->sum('total') - $quote->subtotal
            ) < 0.01,
            'vat_valid' => $this->validateVAT($quote),
            'pdf_template_exists' => view()->exists('quotes.pdf'),
            'company_complete' => $this->checkCompanyDetails($quote->company),
        ];
    }
}
```

## Best Practices

### DO:
- Track all customer interactions
- Follow up on quotes within 3 days
- Document lost deal reasons
- Set realistic close dates
- Update pipeline regularly
- Use lead scoring for prioritization
- Personalize quote cover letters

### DON'T:
- Ignore stale opportunities
- Skip qualification steps
- Over-discount to close deals
- Neglect existing customers
- Forget to log activities
- Create duplicate contacts
- Send quotes without review

## Integration Points

### With Invoice Module
- Quote to invoice conversion
- Payment tracking
- Collection management

### With Accounting Module
- Revenue recognition
- Commission accruals
- Deferred revenue

### With HR Module
- Sales rep targets
- Commission payroll sync

## Related Skills

- **dutch-bookkeeping-expert** - Invoice and revenue accounting
- **dutch-tax-compliance** - BTW handling for sales
- **laravel-expert** - Backend implementation
- **flutter-dart-expert** - Mobile CRM features
