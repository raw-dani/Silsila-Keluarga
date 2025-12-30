<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FamilyMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $members = FamilyMember::with(['father', 'mother', 'spouse'])->get();

        // Load children separately for each member
        $members->each(function ($member) {
            $member->children = $member->children();
        });

        return $members;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:family_members,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'father_id' => 'nullable|exists:family_members,id',
            'mother_id' => 'nullable|exists:family_members,id',
            'spouse_id' => 'nullable|exists:family_members,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();

        // Calculate generation automatically
        $data['generation_level'] = $this->calculateMemberGeneration($data);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $member = FamilyMember::create($data);

        // Ensure bidirectional spouse relationship
        if (!empty($data['spouse_id'])) {
            $spouse = FamilyMember::find($data['spouse_id']);
            if ($spouse && !$spouse->spouse_id) {
                $spouse->update(['spouse_id' => $member->id]);
            }
        }

        // Load children separately since it's not a proper relationship
        $member->children = $member->children();
        return response()->json($member, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $member = FamilyMember::with(['father', 'mother', 'spouse'])->findOrFail($id);

        // Load children separately since it's not a proper relationship
        $member->children = $member->children();

        return response()->json($member);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $member = FamilyMember::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:family_members,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'sometimes|required|in:male,female',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'father_id' => 'nullable|exists:family_members,id',
            'mother_id' => 'nullable|exists:family_members,id',
            'spouse_id' => 'nullable|exists:family_members,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'generation_level' => 'sometimes|required|integer',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Handle spouse relationship changes
        $oldSpouseId = $member->spouse_id;
        $newSpouseId = $data['spouse_id'] ?? null;

        // If spouse changed, update both directions
        if ($oldSpouseId !== $newSpouseId) {
            // Remove old spouse relationship
            if ($oldSpouseId) {
                $oldSpouse = FamilyMember::find($oldSpouseId);
                if ($oldSpouse && $oldSpouse->spouse_id === $member->id) {
                    $oldSpouse->update(['spouse_id' => null]);
                }
            }

            // Create new spouse relationship (bidirectional)
            if ($newSpouseId) {
                $newSpouse = FamilyMember::find($newSpouseId);
                if ($newSpouse && !$newSpouse->spouse_id) {
                    $newSpouse->update(['spouse_id' => $member->id]);
                }
                // Also ensure the member points to the spouse
                $data['spouse_id'] = $newSpouseId;
            }
        }

        $member->update($data);

        // Load children separately since it's not a proper relationship
        $member->children = $member->children();
        return response()->json($member);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $member = FamilyMember::findOrFail($id);

        // Delete photo if exists
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return response()->json(['message' => 'Family member deleted successfully']);
    }

    /**
     * Fix existing spouse relationships to be bidirectional
     */
    public function fixSpouseRelationships()
    {
        $members = FamilyMember::whereNotNull('spouse_id')->get();
        $fixedCount = 0;

        foreach ($members as $member) {
            $spouse = FamilyMember::find($member->spouse_id);

            // If spouse exists and doesn't point back, fix it
            if ($spouse && $spouse->spouse_id !== $member->id) {
                $spouse->update(['spouse_id' => $member->id]);
                $fixedCount++;
            }
        }

        return response()->json([
            'message' => "Fixed $fixedCount spouse relationships",
            'total_processed' => $members->count()
        ]);
    }

    /**
     * Allow authenticated users to update their own profile
     */
    public function updateSelf(Request $request)
    {
        $user = $request->user();

        // Find the family member with this user's email
        $member = FamilyMember::where('email', $user->email)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Family member profile not found for this user'
            ], 404);
        }

        // Validate allowed fields for self-update
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:family_members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date',
            'gender' => 'sometimes|required|in:male,female',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name', 'email', 'phone', 'birth_date', 'death_date', 'gender', 'notes'
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $member->update($data);

        // Load children separately since it's not a proper relationship
        $member->children = $member->children();
        return response()->json($member);
    }

    /**
     * Convert family member to user account
     */
    public function convertToUser(Request $request, $id)
    {
        $member = FamilyMember::findOrFail($id);

        // Check if member has email
        if (!$member->email) {
            return response()->json([
                'message' => 'Cannot convert member to user: No email address provided'
            ], 400);
        }

        // Check if email is used by an admin
        $adminWithEmail = \App\Models\User::where('email', $member->email)
            ->where('role', 'admin')
            ->first();
        if ($adminWithEmail) {
            return response()->json([
                'message' => 'Cannot convert member to user: Email is already used by an admin account'
            ], 400);
        }

        // Check if user already exists with this email
        $existingUser = \App\Models\User::where('email', $member->email)->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'User account already exists with this email address'
            ], 400);
        }

        // Create user account
        $user = \App\Models\User::create([
            'name' => $member->name,
            'email' => $member->email,
            'password' => bcrypt('password123'), // Default password
            'role' => 'member'
        ]);

        return response()->json([
            'message' => 'Member successfully converted to user account',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'default_password' => 'password123'
            ]
        ]);
    }

    /**
     * Get family tree data - Built according to silsilah rules
     */
    public function tree()
    {
        // Langkah 1: Ambil seluruh data anggota keluarga
        $allMembers = FamilyMember::all();

        // Langkah 2: Bangun struktur tree sesuai aturan
        $treeStructure = $this->buildFamilyTree($allMembers);

        return response()->json($treeStructure);
    }

    /**
     * Build family tree structure according to silsilah rules
     */
    private function buildFamilyTree($members)
    {
        // Langkah 1 — Inisialisasi
        $memberMap = [];
        $childrenMap = [];
        $spouseMap = [];

        // Buat map id → member dan inisialisasi children map
        foreach ($members as $member) {
            $memberMap[$member->id] = [
                'id' => $member->id,
                'name' => $member->name,
                'gender' => $member->gender,
                'father_id' => $member->father_id,
                'mother_id' => $member->mother_id,
                'spouse_id' => $member->spouse_id,
                'birth_date' => $member->birth_date,
                'photo' => $member->photo,
                'generation' => null,
                'spouse' => null,
                'children' => []
            ];
            $childrenMap[$member->id] = [];
        }

        // Langkah 2 — Mapping Anak
        foreach ($memberMap as $member) {
            if ($member['father_id'] && isset($memberMap[$member['father_id']])) {
                $childrenMap[$member['father_id']][] = $member['id'];
            }
            if ($member['mother_id'] && isset($memberMap[$member['mother_id']])) {
                $childrenMap[$member['mother_id']][] = $member['id'];
            }
        }

        // Langkah 3 — Pasangan
        // Process spouse relationships in separate steps to avoid data corruption

        // Step 1: Handle explicitly set spouses from database
        foreach ($memberMap as $memberId => $member) {
            if ($member['spouse_id'] && !$member['spouse']) {
                if (isset($memberMap[$member['spouse_id']])) {
                    $memberMap[$memberId]['spouse'] = [
                        'id' => $memberMap[$member['spouse_id']]['id'],
                        'name' => $memberMap[$member['spouse_id']]['name'],
                        'gender' => $memberMap[$member['spouse_id']]['gender']
                    ];
                }
            }
        }

        // Step 2: Ensure bidirectional relationships
        foreach ($memberMap as $memberId => $member) {
            if ($member['spouse'] && isset($memberMap[$member['spouse']['id']])) {
                $spouseId = $member['spouse']['id'];
                if (!$memberMap[$spouseId]['spouse']) {
                    $memberMap[$spouseId]['spouse'] = [
                        'id' => $member['id'],
                        'name' => $member['name'],
                        'gender' => $member['gender']
                    ];
                }
            }
        }

        // Step 3: Detect spouses automatically from common children
        // Create a list of potential spouses based on common children
        $potentialSpouses = [];
        foreach ($childrenMap as $parentId => $childrenIds) {
            if (count($childrenIds) > 0 && !$memberMap[$parentId]['spouse']) {
                foreach ($childrenMap as $otherParentId => $otherChildrenIds) {
                    if ($otherParentId != $parentId &&
                        count($otherChildrenIds) > 0 &&
                        !$memberMap[$otherParentId]['spouse']) {

                        $commonChildren = array_intersect($childrenIds, $otherChildrenIds);
                        if (count($commonChildren) > 0) {
                            // They have common children, so they're spouses
                            $key1 = min($parentId, $otherParentId) . '-' . max($parentId, $otherParentId);
                            if (!isset($potentialSpouses[$key1])) {
                                $potentialSpouses[$key1] = [$parentId, $otherParentId];
                            }
                        }
                    }
                }
            }
        }

        // Apply potential spouses
        foreach ($potentialSpouses as $spousePair) {
            $parentId = $spousePair[0];
            $otherParentId = $spousePair[1];

            $memberMap[$parentId]['spouse'] = [
                'id' => $memberMap[$otherParentId]['id'],
                'name' => $memberMap[$otherParentId]['name'],
                'gender' => $memberMap[$otherParentId]['gender']
            ];
            $memberMap[$otherParentId]['spouse'] = [
                'id' => $memberMap[$parentId]['id'],
                'name' => $memberMap[$parentId]['name'],
                'gender' => $memberMap[$parentId]['gender']
            ];
        }

        // Langkah 4 — Hitung Generasi
        $this->calculateGenerations($memberMap);

        // Langkah 5 — Bangun Struktur Tree - Menantu TIDAK boleh punya tree sendiri
        $treeRoots = [];
        $processedMembers = []; // Track all processed member IDs

        // Hanya pendiri (founders) yang boleh jadi root - menantu mengikuti keluarga silsila
        foreach ($memberMap as $memberId => $member) {
            // Root candidate: tidak punya ayah/ibu
            if (!$member['father_id'] && !$member['mother_id'] && !in_array($memberId, $processedMembers)) {

                // Jika member ini punya spouse, cek apakah spouse juga root candidate
                $hasSpouseAsRoot = false;
                if ($member['spouse']) {
                    $spouseId = $member['spouse']['id'];
                    $spouse = $memberMap[$spouseId];
                    if (!$spouse['father_id'] && !$spouse['mother_id']) {
                        $hasSpouseAsRoot = true;
                    }
                }

                // Jika punya spouse yang juga root, buat grouped node
                if ($hasSpouseAsRoot) {
                    $rootNode = $this->buildRootWithSpouse($memberId, $memberMap, $childrenMap, $processedMembers);
                    if ($rootNode) {
                        $treeRoots[] = $rootNode;
                    }
                } else {
                    // Single root member (tidak punya spouse atau spouse bukan root)
                    $rootNode = $this->buildTreeNode($memberId, $memberMap, $childrenMap);
                    $treeRoots[] = $rootNode;
                    $processedMembers[] = $memberId;
                }
            }
        }

        // Return all root groups - menantu sudah terintegrasi dalam tree pasangan
        return $treeRoots;
    }

    /**
     * Calculate generations for all members
     */
    private function calculateGenerations(&$memberMap)
    {
        // Langkah 1: Hitung generasi berdasarkan keturunan darah (ayah/ibu)
        $this->calculateBloodlineGenerations($memberMap);

        // Langkah 2: Menantu MENGIKUTI generasi suami/istri
        $this->applySpouseGenerations($memberMap);
    }

    /**
     * Calculate generations based on bloodline (father/mother relationships)
     */
    private function calculateBloodlineGenerations(&$memberMap)
    {
        // Cari root members (true ancestors - no parents)
        $queue = [];
        foreach ($memberMap as $memberId => $member) {
            if (!$member['father_id'] && !$member['mother_id']) {
                $memberMap[$memberId]['generation'] = 1;
                $queue[] = $memberId;
            }
        }

        // BFS untuk menghitung generasi berdasarkan keturunan darah
        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $currentMember = $memberMap[$currentId];
            $currentGen = $currentMember['generation'];

            // Cari anak-anak berdasarkan ayah/ibu - use copy to avoid modifying while iterating
            $memberMapCopy = $memberMap;
            foreach ($memberMapCopy as $childId => $childMember) {
                if (($childMember['father_id'] == $currentId || $childMember['mother_id'] == $currentId) &&
                    (!$childMember['generation'] || $childMember['generation'] > $currentGen + 1)) {
                    $memberMap[$childId]['generation'] = $currentGen + 1;
                    $queue[] = $childId;
                }
            }
        }
    }

    /**
     * Apply spouse generations - menantu MENGIKUTI generasi suami/istri
     */
    private function applySpouseGenerations(&$memberMap)
    {
        // Setelah semua generasi keturunan darah ditentukan,
        // menantu wajib mengikuti generasi suami/istrinya
        foreach ($memberMap as $memberId => $member) {
            if ($member['spouse'] && $member['generation']) {
                $spouseId = $member['spouse']['id'];
                // Menantu WAJIB mengikuti generasi pasangannya
                $memberMap[$spouseId]['generation'] = $member['generation'];
            }
        }
    }

    /**
     * Build tree node recursively
     */
    private function buildTreeNode($memberId, &$memberMap, &$childrenMap)
    {
        $member = $memberMap[$memberId];

        // Urutkan anak berdasarkan birth_date
        $childrenIds = isset($childrenMap[$memberId]) ? $childrenMap[$memberId] : [];
        usort($childrenIds, function($a, $b) use ($memberMap) {
            $dateA = $memberMap[$a]['birth_date'] ? strtotime($memberMap[$a]['birth_date']) : PHP_INT_MAX;
            $dateB = $memberMap[$b]['birth_date'] ? strtotime($memberMap[$b]['birth_date']) : PHP_INT_MAX;
            return $dateA <=> $dateB;
        });

        $children = [];
        foreach ($childrenIds as $childId) {
            // Hindari duplikasi jika anak sudah diproses
            $childAlreadyProcessed = false;
            foreach ($children as $existingChild) {
                if ($existingChild['id'] == $childId) {
                    $childAlreadyProcessed = true;
                    break;
                }
            }
            if (!$childAlreadyProcessed) {
                $children[] = $this->buildTreeNode($childId, $memberMap, $childrenMap);
            }
        }

        return [
            'id' => $member['id'],
            'name' => $member['name'],
            'gender' => $member['gender'],
            'generation' => $member['generation'],
            'birth_date' => $member['birth_date'],
            'photo' => $member['photo'],
            'spouse' => $member['spouse'],
            'children' => $children
        ];
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

    /**
     * Build root node with spouse grouping
     */
    private function buildRootWithSpouse($memberId, &$memberMap, &$childrenMap, &$processedMembers)
    {
        $member = $memberMap[$memberId];
        $processedMembers[] = $memberId;

        // Collect all children from this member and their spouse
        $allChildrenIds = isset($childrenMap[$memberId]) ? $childrenMap[$memberId] : [];

        // If member has spouse, add spouse's children too and mark spouse as processed
        if ($member['spouse']) {
            $spouseId = $member['spouse']['id'];
            if (isset($memberMap[$spouseId])) {
                $processedMembers[] = $spouseId;
                $spouseChildrenIds = isset($childrenMap[$spouseId]) ? $childrenMap[$spouseId] : [];
                $allChildrenIds = array_unique(array_merge($allChildrenIds, $spouseChildrenIds));
            }
        }

        // Sort children by birth date
        usort($allChildrenIds, function($a, $b) use ($memberMap) {
            $dateA = $memberMap[$a]['birth_date'] ? strtotime($memberMap[$a]['birth_date']) : PHP_INT_MAX;
            $dateB = $memberMap[$b]['birth_date'] ? strtotime($memberMap[$b]['birth_date']) : PHP_INT_MAX;
            return $dateA <=> $dateB;
        });

        // Build children nodes
        $children = [];
        foreach ($allChildrenIds as $childId) {
            if (!in_array($childId, $processedMembers)) {
                $children[] = $this->buildTreeNode($childId, $memberMap, $childrenMap);
                $processedMembers[] = $childId;
            }
        }

        return [
            'id' => $member['id'],
            'name' => $member['name'],
            'gender' => $member['gender'],
            'generation' => $member['generation'],
            'birth_date' => $member['birth_date'],
            'photo' => $member['photo'],
            'spouse' => $member['spouse'],
            'children' => $children
        ];
    }

    private function buildTree($member)
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'gender' => $member->gender,
            'birth_date' => $member->birth_date,
            'death_date' => $member->death_date,
            'photo' => $member->photo,
            'generation_level' => $member->generation_level,
            'notes' => $member->notes,
            'spouse' => $member->spouse ? [
                'id' => $member->spouse->id,
                'name' => $member->spouse->name,
            ] : null,
            'children' => $member->children->map(function ($child) {
                return $this->buildTree($child);
            }),
        ];
    }
}
