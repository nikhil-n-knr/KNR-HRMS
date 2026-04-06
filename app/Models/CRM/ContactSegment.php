<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CRM\Contact;

class ContactSegment extends Model
{
    use HasFactory;

    protected $table = 'crm_contact_segments';

    protected $fillable = [
        'tenant_id', 'name', 'description', 'criteria', 'is_public', 'created_by'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_public' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'crm_contact_segment_pivot', 'contact_segment_id', 'contact_id')
            ->withTimestamps();
    }

    /**
     * Get the contacts that match this segment's criteria.
     * This method builds a query dynamically.
     */
    public function getContactsQuery()
    {
        $query = Contact::where('tenant_id', $this->tenant_id);

        if (!empty($this->criteria) && is_array($this->criteria)) {
            foreach ($this->criteria as $rule) {
                if (isset($rule['field'], $rule['operator'], $rule['value'])) {
                    // Simple recursive or linear query building
                    $this->applyRule($query, $rule);
                }
            }
        }

        return $query;
    }

    protected function applyRule($query, $rule)
    {
        $field = $rule['field'];
        $operator = $rule['operator'];
        $value = $rule['value'];

        // Handle specific fields or relations if needed
        // For now, assume flat fields on Contact model

        switch ($operator) {
            case 'contains':
                $query->where($field, 'like', "%{$value}%");
                break;
            case 'starts_with':
                $query->where($field, 'like', "{$value}%");
                break;
            case 'ends_with':
                $query->where($field, 'like', "%{$value}");
                break;
            case 'is_empty':
                $query->whereNull($field)->orWhere($field, '');
                break;
            case 'is_not_empty':
                $query->whereNotNull($field)->where($field, '!=', '');
                break;
            default: // =, !=, >, <, >=, <=
                $query->where($field, $operator, $value);
                break;
        }
    }
}
