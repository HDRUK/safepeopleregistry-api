<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="ProjectDetail",
 *      title="Project Detail",
 *      description="ProjectDetail model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="project_id",
 *          type="integer",
 *          example=1,
 *          description="Primary key of associated Project for this ProjectDetail"
 *      ),
 *      @OA\Property(property="datasets",
 *          type="array",
 *          example="[
 *              'https://url.to.dataset/1',
 *              'https://url.to.dataset/2'
 *          ]",
 *          @OA\Items()
 *      ),
 *      @OA\Property(property="other_approval_committees",
 *          type="array",
 *          example="[
 *              'Name and description of approval committee'
 *          ]",
 *          @OA\Items()
 *      ),
 *      @OA\Property(property="data_sensitivity_level",
 *          type="string",
 *          example="Anonymous"
 *      ),
 *      @OA\Property(property="legal_basis_for_data_article6",
 *          type="string",
 *          example="Legal basis..."
 *      ),
 *      @OA\Property(property="duty_of_confidentiality",
 *          type="bool",
 *          example="true"
 *      ),
 *      @OA\Property(property="national_data_optout",
 *          type="bool",
 *          example="false"
 *      ),
 *      @OA\Property(property="request_frequency",
 *          type="string",
 *          enum={"ONE-OFF", "RECURRING"}
 *      ),
 *      @OA\Property(property="dataset_linkage_description",
 *          type="string",
 *          example="Datasets are linked by means of..."
 *      ),
 *      @OA\Property(property="data_minimisation",
 *          type="string",
 *          example="Approach to data minimisation is..."
 *      ),
 *      @OA\Property(property="data_use_description",
 *          type="string",
 *          example="Description of the data being used..."
 *      ),
 *      @OA\Property(property="access_date",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="access_type",
 *          type="integer",
 *          example=0
 *      ),
 *      @OA\Property(property="data_privacy",
 *          type="string",
 *          example="Our data privacy methods are..."
 *      ),
 *      @OA\Property(property="research_outputs",
 *          type="object",
 *          example={
*               "https://yourdomain.com/research_output_1",
*               "https://yourdomain.com/research_output_2"
 *          }
 *      ),
 *      @OA\Property(property="data_assets",
 *          type="string",
 *          example="Our data assets are..."
 *      )
 * )
 */
class ProjectDetail extends Model
{
    use HasFactory;

    protected $table = 'project_details';

    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'datasets',
        'other_approval_committees',
        'data_sensitivity_level',
        'legal_basis_for_data_article6',
        'duty_of_confidentiality',
        'national_data_optout',
        'request_frequency',
        'dataset_linkage_description',
        'data_minimisation',
        'data_use_description',
        'access_date',
        'access_type',
        'data_privacy',
        'research_outputs',
        'data_assets',
    ];
}
