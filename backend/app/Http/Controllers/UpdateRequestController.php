<?php

namespace App\Http\Controllers;

use App\Models\UpdateRequest;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $requests = UpdateRequest::with(['member', 'targetMember'])
            ->where('status', $status)
            ->get();

        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'target_member_id' => 'required_if:change_type,biodata,hubungan,foto|exists:family_members,id',
            'change_type' => 'required|in:biodata,hubungan,foto,add_member',
            'member_data' => 'required_if:change_type,add_member|array',
            'new_data' => 'required_if:change_type,biodata,hubungan,foto|string',
        ]);

        $user = $request->user();

        $data = [
            'member_id' => $user->id,
            'change_type' => $request->change_type,
            'old_data' => null,
        ];

        if ($request->change_type === 'add_member') {
            // For add_member requests, store member data and set target_member_id from request
            $data['target_member_id'] = $request->target_member_id;
            $data['new_data'] = json_encode($request->member_data);
        } else {
            // For update requests, get old data from existing member
            $targetMember = FamilyMember::findOrFail($request->target_member_id);
            $data['target_member_id'] = $request->target_member_id;
            $data['old_data'] = $this->getOldData($targetMember, $request->change_type);
            $data['new_data'] = $request->new_data;
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('request_photos', 'public');
        }

        $updateRequest = UpdateRequest::create($data);

        return response()->json($updateRequest->load(['member', 'targetMember']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $request = UpdateRequest::with(['member', 'targetMember'])->findOrFail($id);
        return response()->json($request);
    }

    /**
     * Approve the update request
     */
    public function approve(string $id)
    {
        $updateRequest = UpdateRequest::findOrFail($id);
        $updateRequest->status = 'approved';
        $updateRequest->admin_note = request('admin_note');
        $updateRequest->save();

        // Apply the changes to the family member
        $this->applyChanges($updateRequest);

        return response()->json(['message' => 'Request approved and changes applied']);
    }

    /**
     * Reject the update request
     */
    public function reject(string $id)
    {
        $updateRequest = UpdateRequest::findOrFail($id);
        $updateRequest->status = 'rejected';
        $updateRequest->admin_note = request('admin_note');
        $updateRequest->save();

        return response()->json(['message' => 'Request rejected']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $request = UpdateRequest::findOrFail($id);

        // Delete photo if exists
        if ($request->photo) {
            Storage::disk('public')->delete($request->photo);
        }

        $request->delete();

        return response()->json(['message' => 'Update request deleted']);
    }

    private function getOldData($member, $changeType)
    {
        switch ($changeType) {
            case 'biodata':
                return json_encode([
                    'name' => $member->name,
                    'birth_date' => $member->birth_date,
                    'death_date' => $member->death_date,
                    'notes' => $member->notes,
                ]);
            case 'hubungan':
                return json_encode([
                    'father_id' => $member->father_id,
                    'mother_id' => $member->mother_id,
                    'spouse_id' => $member->spouse_id,
                ]);
            case 'foto':
                return $member->photo;
            default:
                return null;
        }
    }

    private function applyChanges($updateRequest)
    {
        $newData = json_decode($updateRequest->new_data, true);

        switch ($updateRequest->change_type) {
            case 'add_member':
                // Create new family member
                $memberData = $newData;

                // Remove form-specific fields that are not database columns
                unset($memberData['relationship_type']);
                unset($memberData['related_member_id']);
                unset($memberData['photo']); // Photo is handled separately via $updateRequest->photo

                // Handle photo upload if present
                if ($updateRequest->photo) {
                    // Move photo from request_photos to photos directory
                    $oldPath = $updateRequest->photo;
                    $fileName = basename($oldPath);
                    $newPath = 'photos/' . $fileName;

                    // Move the file
                    Storage::disk('public')->move($oldPath, $newPath);
                    $memberData['photo'] = $newPath;
                }

                // Set relationship based on the original request data (stored in new_data)
                $relationshipType = $newData['relationship_type'] ?? 'child';

                if ($relationshipType === 'child') {
                    // For child: target_member becomes one parent, and their spouse becomes the other parent
                    $targetMember = $updateRequest->targetMember;
                    if ($targetMember) {
                        if ($targetMember->gender === 'male') {
                            $memberData['father_id'] = $targetMember->id;
                            // If father has a spouse, set as mother
                            if ($targetMember->spouse_id) {
                                $memberData['mother_id'] = $targetMember->spouse_id;
                            }
                        } else {
                            $memberData['mother_id'] = $targetMember->id;
                            // If mother has a spouse, set as father
                            if ($targetMember->spouse_id) {
                                $memberData['father_id'] = $targetMember->spouse_id;
                            }
                        }
                    }
                } elseif ($relationshipType === 'spouse') {
                    // For spouse: target_member becomes the spouse
                    $memberData['spouse_id'] = $updateRequest->target_member_id;
                }

                // Ensure all values are properly cast to strings or null
                foreach ($memberData as $key => $value) {
                    if ($value === '' || $value === null) {
                        $memberData[$key] = null;
                    } elseif (is_array($value)) {
                        // Convert arrays to JSON strings for storage
                        $memberData[$key] = json_encode($value);
                    } else {
                        // Ensure value is a string
                        $memberData[$key] = (string) $value;
                    }
                }

                // Calculate generation level automatically (same as FamilyMemberController)
                $memberData['generation_level'] = $this->calculateMemberGeneration($memberData);

                $newMember = FamilyMember::create($memberData);

                // For spouse relationships, also set the reverse relationship
                if ($relationshipType === 'spouse' && $updateRequest->target_member_id) {
                    $spouse = FamilyMember::find($updateRequest->target_member_id);
                    if ($spouse && !$spouse->spouse_id) {
                        $spouse->update(['spouse_id' => $newMember->id]);
                    }
                }

                break;

            case 'biodata':
                $member = $updateRequest->targetMember;
                $member->update($newData);
                break;

            case 'hubungan':
                $member = $updateRequest->targetMember;
                $member->update($newData);
                break;

            case 'foto':
                $member = $updateRequest->targetMember;
                if ($member->photo) {
                    Storage::disk('public')->delete($member->photo);
                }
                $member->photo = $updateRequest->photo;
                $member->save();
                break;
        }
    }

    /**
     * Calculate generation for a new member based on parents or spouse
     */
    private function calculateMemberGeneration($memberData)
    {
        // If member has a spouse, they MUST follow the spouse's generation
        if (!empty($memberData['spouse_id'])) {
            $spouse = FamilyMember::find($memberData['spouse_id']);
            if ($spouse) {
                return $spouse->generation_level;
            }
        }

        // If no spouse, calculate based on parents
        // If no parents, this is generation 1 (root)
        if (empty($memberData['father_id']) && empty($memberData['mother_id'])) {
            return 1;
        }

        // Find parent's generation
        $parentGeneration = 0;

        if (!empty($memberData['father_id'])) {
            $father = FamilyMember::find($memberData['father_id']);
            if ($father) {
                $parentGeneration = max($parentGeneration, $father->generation_level);
            }
        }

        if (!empty($memberData['mother_id'])) {
            $mother = FamilyMember::find($memberData['mother_id']);
            if ($mother) {
                $parentGeneration = max($parentGeneration, $mother->generation_level);
            }
        }

        // Child generation = parent generation + 1
        return $parentGeneration + 1;
    }
}
