<template>
  <div class="family-tree-page">
    <Navigation />

    <div class="family-tree-content">
      <div class="tree-header">
        <h1>Pohon Keluarga</h1>
        <p>Visualisasi interaktif silsilah keluarga dengan fitur zoom dan pan</p>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <div class="search-container">
          <div class="search-input-wrapper">
            <input
              type="text"
              v-model="searchQuery"
              @input="handleSearch"
              @keydown.enter="searchMembers"
              placeholder="Cari nama anggota keluarga..."
              class="search-input"
            >
            <button @click="searchMembers" class="search-btn">
              <span class="btn-icon">🔍</span>
            </button>
            <button v-if="searchQuery" @click="clearSearch" class="clear-btn">
              <span class="btn-icon">✕</span>
            </button>
          </div>

          <!-- Search Results -->
          <div v-if="searchResults.length > 0" class="search-results">
            <div class="results-header">
              <span>Ditemukan {{ searchResults.length }} hasil</span>
            </div>
            <div class="results-list">
              <div
                v-for="result in searchResults"
                :key="result.id"
                @click="focusOnMember(result.id)"
                class="result-item"
                :class="{ active: highlightedMember === result.actualId }"
              >
                <div class="result-avatar">
                  <img v-if="result.photo" :src="getPhotoUrl(result.photo)" :alt="result.name">
                  <span v-else class="avatar-placeholder">
                    {{ result.gender === 'male' ? '👨' : result.gender === 'female' ? '👩' : '👤' }}
                  </span>
                </div>
                <div class="result-info">
                  <div class="result-name">{{ result.name }}</div>
                <div class="result-details">
                  <span v-if="shouldShowGeneration(result)" class="generation">{{ getGenerationName(result.generation) }}</span>
                  <span v-if="result.isSpouseHighlight" class="spouse-note">{{ result.spouse?.name }}</span>
                </div>
                </div>
                <button @click.stop="focusOnMember(result.id)" class="focus-btn">
                  <span class="btn-icon">📍</span>
                </button>
              </div>
            </div>
          </div>

          <div v-if="searchQuery && searchResults.length === 0 && !searching" class="no-results">
            <span class="no-results-icon">🔍</span>
            <span>Tidak ditemukan "{{ searchQuery }}"</span>
          </div>
        </div>
      </div>

      <div class="tree-controls">
        <button @click="zoomIn" class="control-btn">
          <span class="btn-icon">➕</span>
          <span>Zoom In</span>
        </button>
        <button @click="zoomOut" class="control-btn">
          <span class="btn-icon">➖</span>
          <span>Zoom Out</span>
        </button>
        <button @click="resetZoom" class="control-btn">
          <span class="btn-icon">🔄</span>
          <span>Reset</span>
        </button>
        <button @click="centerTree" class="control-btn">
          <span class="btn-icon">📍</span>
          <span>Center</span>
        </button>
        <button v-if="highlightedMember" @click="clearHighlight" class="control-btn highlight-btn">
          <span class="btn-icon">🎯</span>
          <span>Clear Highlight</span>
        </button>
      </div>

      <div id="tree-container" ref="treeContainer">
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>Loading pohon keluarga...</p>
        </div>
        <div v-if="!loading && familyData.length === 0" class="no-data">
          <div class="empty-icon">🌳</div>
          <h3>Belum ada data keluarga</h3>
          <p>Silakan tambah anggota keluarga terlebih dahulu</p>
        </div>
        <svg v-if="!loading && familyData.length > 0" ref="svgElement" class="tree-svg"></svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import * as d3 from 'd3'
import api from '../services/api'
import Navigation from '../components/Navigation.vue'

const router = useRouter()
const treeContainer = ref(null)
const svgElement = ref(null)
const loading = ref(true)
const familyData = ref([])

// Search related variables
const searchQuery = ref('')
const searchResults = ref([])
const highlightedMember = ref(null)
const searching = ref(false)

let svg = null
let g = null
let zoom = null
let treeLayout = null
let root = null

const getPhotoUrl = (photoPath) => {
  if (!photoPath) return '/default-avatar.png'

  // If it's already a full URL, return as is
  if (photoPath.startsWith('http')) {
    return photoPath
  }

  // If it's a relative path (stored in storage), construct the full URL
  // Backend serves storage files on port 8000, frontend is on port 5173
  const backendUrl = window.location.origin.replace('5173', '8000')
  return `${backendUrl}/storage/${photoPath}`
}

onMounted(async () => {
  await loadFamilyData()
  await nextTick()
  renderTree()
  window.addEventListener('resize', handleResize)
  // Make showMemberDetails available globally
  window.showMemberDetails = showMemberDetails
})

onUnmounted(() => {
  if (svg) {
    svg.selectAll('*').remove()
  }
  window.removeEventListener('resize', handleResize)
})

const loadFamilyData = async () => {
  try {
    const response = await api.get('/family-tree')
    familyData.value = response.data
    console.log('Family data loaded:', familyData.value)
  } catch (error) {
    console.error('Error loading family tree:', error)
  } finally {
    loading.value = false
  }
}

const renderTree = () => {
  if (!svgElement.value || !familyData.value || familyData.value.length === 0) {
    console.log('No container or data, skipping render')
    return
  }

  try {
    // Clear previous SVG
    d3.select(svgElement.value).selectAll('*').remove()

    // Get container dimensions
    const containerRect = treeContainer.value.getBoundingClientRect()
    const width = containerRect.width
    const height = Math.max(600, containerRect.height)

    // Create SVG
    svg = d3.select(svgElement.value)
      .attr('width', width)
      .attr('height', height)
      .attr('viewBox', [0, 0, width, height])

    // Create zoom behavior
    zoom = d3.zoom()
      .scaleExtent([0.1, 3])
      .on('zoom', (event) => {
        g.attr('transform', event.transform)
      })

    svg.call(zoom)

    // Create main group
    g = svg.append('g')
      .attr('transform', `translate(${width/2}, 50)`)

    // Create tree layout
    treeLayout = d3.tree()
      .nodeSize([120, 200]) // [vertical spacing, horizontal spacing]
      .separation((a, b) => a.parent === b.parent ? 1 : 1.5)

    // Convert data to hierarchical format
    const hierarchicalData = convertToHierarchicalFormat(familyData.value)

    // Create root node
    root = d3.hierarchy(hierarchicalData)

    // Apply tree layout
    treeLayout(root)

    // Create links (lines connecting nodes)
    const link = g.selectAll('.link')
      .data(root.links())
      .enter().append('path')
      .attr('class', 'link')
      .attr('d', d => `
        M${d.source.x},${d.source.y}
        C${d.source.x},${(d.source.y + d.target.y) / 2}
         ${d.target.x},${(d.source.y + d.target.y) / 2}
         ${d.target.x},${d.target.y}
      `)
      .style('fill', 'none')
      .style('stroke', 'var(--primary)')
      .style('stroke-width', 2)

    // Create nodes
    const node = g.selectAll('.node')
      .data(root.descendants())
      .enter().append('g')
      .attr('class', d => `node ${d.data.gender}`)
      .attr('transform', d => `translate(${d.x}, ${d.y})`)
      .style('cursor', 'pointer')
      .on('click', (event, d) => showMemberDetails(d.data.id))

    // Add circles for nodes (background)
    node.append('circle')
      .attr('r', 35)
      .style('fill', d => getNodeColor(d.data.gender))
      .style('stroke', '#fff')
      .style('stroke-width', 3)

    // Add avatars (if available)
    node.each(function(d) {
      const nodeGroup = d3.select(this)
      if (d.data.photo) {
        // Create full URL for uploaded photos
        const photoUrl = getPhotoUrl(d.data.photo)
        nodeGroup.append('image')
          .attr('xlink:href', photoUrl)
          .attr('x', -25)
          .attr('y', -25)
          .attr('width', 50)
          .attr('height', 50)
          .attr('clip-path', 'circle()')
          .on('error', function() {
            // If image fails to load, show emoji instead
            d3.select(this).remove()
            nodeGroup.append('text')
              .attr('text-anchor', 'middle')
              .attr('dy', '0.35em')
              .attr('font-size', '20px')
              .text(d.data.gender === 'male' ? '👨' : d.data.gender === 'female' ? '👩' : '👤')
          })
      } else {
        // Add default avatar placeholder
        nodeGroup.append('text')
          .attr('text-anchor', 'middle')
          .attr('dy', '0.35em')
          .attr('font-size', '20px')
          .text(d.data.gender === 'male' ? '👨' : d.data.gender === 'female' ? '👩' : '👤')
      }
    })

    // Add generation info (stroke background first, then fill on top)
    // const genText = d => {
    //   const genName = getGenerationName(d.data.generation)
    //   console.log(`${d.data.name}: generation=${d.data.generation}, display=${genName}`)
    //   return genName
    // }

    // // Generation stroke background
    // node.append('text')
    //   .attr('dy', 75)
    //   .attr('text-anchor', 'middle')
    //   .attr('font-size', '10px')
    //   .style('fill', 'none')
    //   .style('stroke', '#ffffff')
    //   .style('stroke-width', '4px')
    //   .style('stroke-linejoin', 'round')
    //   .style('stroke-linecap', 'round')
    //   .style('pointer-events', 'none')
    //   .text(genText)

    // // Generation fill foreground
    // node.append('text')
    //   .attr('dy', 75)
    //   .attr('text-anchor', 'middle')
    //   .attr('font-size', '10px')
    //   .style('fill', '#000000')
    //   .style('stroke', 'none')
    //   .style('pointer-events', 'none')
    //   .text(genText)

    // Add names with spouse display (stroke background first, then fill on top)
    const nameText = d => {
      if (d.data.spouse) {
        // Display spouse relationship: "name <- spouse"
        return `${d.data.name} ← ${d.data.spouse.name}`
      }
      return d.data.name
    }

    // Name stroke background
    node.append('text')
      .attr('dy', 5)
      .attr('text-anchor', 'middle')
      .attr('font-size', '11px')
      .attr('font-weight', 'bold')
      .style('fill', 'none')
      .style('stroke', '#ffffff')
      .style('stroke-width', '4px')
      .style('stroke-linejoin', 'round')
      .style('stroke-linecap', 'round')
      .style('pointer-events', 'none')
      .text(nameText)
      .call(wrapText, 120)

    // Name fill foreground
    node.append('text')
      .attr('dy', 5)
      .attr('text-anchor', 'middle')
      .attr('font-size', '11px')
      .attr('font-weight', 'bold')
      .style('fill', '#000000')
      .style('stroke', 'none')
      .style('pointer-events', 'none')
      .text(nameText)
      .call(wrapText, 120)

    // Center the tree initially
    centerTree()

    console.log('D3.js family tree rendered successfully')

  } catch (error) {
    console.error('Error rendering D3 tree:', error)
    const errorDiv = document.createElement('div')
    errorDiv.className = 'error'
    errorDiv.textContent = 'Error rendering tree: ' + error.message
    treeContainer.value.appendChild(errorDiv)
  }
}

const convertToHierarchicalFormat = (treeData) => {
  console.log('Processing pre-built tree data:', treeData)

  if (!treeData || treeData.length === 0) {
    return { name: 'No Data', children: [] }
  }

  // Filter out any nodes that should not be parallel to founders (generation 1)
  // Only generation 1 members should appear at root level
  const filteredTreeData = treeData.filter(member => {
    // Always show generation 1 members (founders) regardless of spouse/children status
    if (member.generation === 1) {
      console.log('Showing founder:', member.name, 'generation:', member.generation)
      return true
    }

    // Hide non-founder nodes (generation > 1)
    if (member.generation > 1) {
      console.log('Hiding non-founder node:', member.name, 'generation:', member.generation)
      return false
    }

    // Hide generation 0 (virtual root) or unknown generations
    if (member.generation <= 0) {
      console.log('Hiding root/virtual node:', member.name, 'generation:', member.generation)
      return false
    }

    console.log('Unexpected case for:', member.name, 'generation:', member.generation)
    return false
  })

  if (filteredTreeData.length === 0) {
    return { name: 'No Data', children: [] }
  }

  // Backend returns array of root members, create a virtual root for multiple roots
  if (filteredTreeData.length === 1) {
    return filteredTreeData[0]
  } else {
    return {
      id: 'virtual-root',
      name: 'Keluarga',
      gender: 'unknown',
      generation: 0,
      spouse: null,
      children: filteredTreeData
    }
  }
}

const shouldShowGeneration = (result) => {
  // Don't show generation info if member doesn't have parents (except for root family members)
  // Root family members (generation 1) always show generation
  if (result.generation === 1) return true

  // For other members, only show generation if they have parents
  return result.father_id || result.mother_id
}

const getGenerationName = (generation) => {
  // Find the minimum generation in the family tree (root generation)
  const minGeneration = Math.min(...familyData.value.map(member => member.generation).filter(gen => gen > 0))

  // Calculate relative generation from root
  const relativeGeneration = generation - minGeneration + 1

  const generationNames = {
    0: 'Keluarga',
    1: 'Generasi 1', // Root generation
    2: 'Generasi 2', // Children generation
    3: 'Generasi 3', // Grandchildren generation
    4: 'Generasi 4', // Great-grandchildren generation
    5: 'Generasi 5', // Great-great-grandchildren generation
    6: 'Generasi 6', // Further generations
    7: 'Generasi 7',
    8: 'Generasi 8',
    9: 'Generasi 9',
    10: 'Generasi 10'
  }

  return generationNames[relativeGeneration] || `Generasi ${relativeGeneration}`
}

const getNodeColor = (gender) => {
  switch (gender) {
    case 'male': return 'var(--secondary)' // Coklat emas for male
    case 'female': return 'var(--accent)' // Beige emas for female
    default: return 'var(--text-muted)' // Gray for unknown
  }
}

const wrapText = (text, width) => {
  text.each(function() {
    const text = d3.select(this)
    const words = text.text().split(/\s+/).reverse()
    let word
    let line = []
    let lineNumber = 0
    const lineHeight = 1.1
    const y = text.attr('y')
    const dy = parseFloat(text.attr('dy'))
    let tspan = text.text(null).append('tspan').attr('x', 0).attr('y', y).attr('dy', dy + 'em')

    while (word = words.pop()) {
      line.push(word)
      tspan.text(line.join(' '))
      if (tspan.node().getComputedTextLength() > width) {
        line.pop()
        tspan.text(line.join(' '))
        line = [word]
        tspan = text.append('tspan').attr('x', 0).attr('y', y).attr('dy', ++lineNumber * lineHeight + dy + 'em').text(word)
      }
    }
  })
}

const showMemberDetails = (memberId) => {
  // Navigate to the profile page for detailed member information
  router.push(`/profile/${memberId}`)
}

const zoomIn = () => {
  if (svg && zoom) {
    svg.transition().duration(300).call(
      zoom.scaleBy, 1.5
    )
  }
}

const zoomOut = () => {
  if (svg && zoom) {
    svg.transition().duration(300).call(
      zoom.scaleBy, 1/1.5
    )
  }
}

const resetZoom = () => {
  if (svg && zoom) {
    svg.transition().duration(300).call(
      zoom.transform,
      d3.zoomIdentity.translate(svg.attr('width')/2, 50).scale(1)
    )
  }
}

const centerTree = () => {
  if (svg && zoom && root) {
    const bounds = g.node().getBBox()
    const fullWidth = svg.attr('width')
    const fullHeight = svg.attr('height')
    const scale = Math.min(fullWidth / bounds.width, fullHeight / bounds.height) * 0.8
    const translateX = fullWidth/2 - (bounds.x + bounds.width/2) * scale
    const translateY = 50 - bounds.y * scale

    svg.call(
      zoom.transform,
      d3.zoomIdentity.translate(translateX, translateY).scale(scale)
    )
  }
}

const handleResize = () => {
  if (svg) {
    centerTree()
  }
}

// Search functions
const handleSearch = () => {
  // Debounced search
  clearTimeout(window.searchTimeout)
  searching.value = true

  window.searchTimeout = setTimeout(() => {
    searchMembers()
  }, 300)
}

const searchMembers = () => {
  if (!searchQuery.value.trim()) {
    searchResults.value = []
    return
  }

  const query = searchQuery.value.toLowerCase().trim()
  const foundMembers = new Map() // Use Map to deduplicate by ID

  // Flatten the tree data for search
  const flattenTree = (nodes) => {
    nodes.forEach(node => {
      if (node.name && node.name.toLowerCase().includes(query)) {
        // Skip if already found
        if (foundMembers.has(node.id)) return

        // Check spouse relationship - determine if this member should show spouse highlight note
        let actualId = node.id
        let isSpouseHighlight = false
        let spouseName = ''

        // If member has a spouse, set up spouse highlighting info
        if (node.spouse_id) {
          const spouse = familyData.value.find(m => m.id == node.spouse_id)
          if (spouse) {
            spouseName = spouse.name

            // Check if this is a menantu (spouse who married into the family)
            // Logic: generation > 1 AND spouse is generation 1 (married to founder = menantu)
            const hasParents = node.father_id || node.mother_id

            // Menantu logic: generation > 1 AND married to founder (gen 1) AND no parents
            // Menantu tidak memiliki ayah maupun ibu (they're from outside the family)
            if (node.generation > 1 && spouse.generation === 1 && !hasParents) {
              // This member is a menantu: higher generation, married to founder, no parents
              isSpouseHighlight = true
              actualId = spouse.id // Highlight the founder spouse
              console.log(`✅ MENANTU FOUND: ${node.name} (gen ${node.generation}) is menantu of founder ${spouse.name} (gen ${spouse.generation}) - no parents as expected`)
            } else if (node.generation === 1 && spouse.generation > 1) {
              // Member is founder, spouse is potentially menantu
              // Don't show menantu note when searching for founder
              actualId = node.id
              isSpouseHighlight = false
              console.log(`Founder ${node.name} (gen ${node.generation}) has spouse ${spouse.name} (gen ${spouse.generation})`)
            } else if (node.generation > 1 && spouse.generation === 1 && hasParents) {
              // Has parents and married to founder - likely a family member who married
              actualId = node.id
              isSpouseHighlight = false
              console.log(`Family member ${node.name} (gen ${node.generation}) married to founder ${spouse.name} but has parents - not menantu`)
            } else {
              // Other cases (same generation or other patterns)
              actualId = node.id
              isSpouseHighlight = false
              console.log(`Other case: ${node.name} (gen ${node.generation}) and ${spouse.name} (gen ${spouse.generation})`)
            }
          } else {
            console.log(`Spouse not found for ${node.name}, spouse_id: ${node.spouse_id}`)
          }
        }

        foundMembers.set(node.id, {
          ...node,
          actualId: actualId,
          isSpouseHighlight: isSpouseHighlight,
          spouseName: spouseName
        })
      }

      if (node.children && node.children.length > 0) {
        flattenTree(node.children)
      }
    })
  }

  // Search through all family data (not just visible tree)
  flattenTree(familyData.value)

  // Convert Map to array
  const results = Array.from(foundMembers.values())
  searchResults.value = results
  searching.value = false

  console.log(`Search results for "${query}":`, results)
}

const focusOnMember = (memberId) => {
  let actualMemberId = memberId
  let isSpouseHighlight = false
  let spouseName = ''

  // Check if this member is visible in the tree
  const originalNode = root.descendants().find(d => d.data.id == memberId)

  if (!originalNode) {
    // Member not in tree, try to find their spouse
    const member = familyData.value.find(m => m.id == memberId)
    if (member && member.spouse_id) {
      const spouse = familyData.value.find(m => m.id == member.spouse_id)
      if (spouse) {
        // Check if spouse is in the tree
        const spouseNode = root.descendants().find(d => d.data.id == member.spouse_id)
        if (spouseNode) {
          actualMemberId = member.spouse_id
          isSpouseHighlight = true
          spouseName = spouse.name
          console.log(`Member ${member.name} not in tree, highlighting spouse ${spouse.name}`)
        }
      }
    }
  }

  highlightedMember.value = actualMemberId

  if (!root || !svg || !zoom) return

  // Find the node in the tree
  const targetNode = root.descendants().find(d => d.data.id == actualMemberId)

  if (targetNode) {
    // Calculate the transform to center on this node
    const containerRect = treeContainer.value.getBoundingClientRect()
    const scale = 1.2 // Slightly zoomed in for better focus
    const translateX = containerRect.width / 2 - targetNode.x * scale
    const translateY = containerRect.height / 2 - targetNode.y * scale

    // Apply the transform
    svg.transition().duration(750).call(
      zoom.transform,
      d3.zoomIdentity.translate(translateX, translateY).scale(scale)
    )

    // Highlight the node
    highlightNode(actualMemberId)
  }

  // Update the result item to show spouse highlight info
  const resultIndex = searchResults.value.findIndex(r => r.id === memberId)
  if (resultIndex !== -1) {
    searchResults.value[resultIndex].actualId = actualMemberId
    searchResults.value[resultIndex].isSpouseHighlight = isSpouseHighlight
    searchResults.value[resultIndex].spouseName = spouseName
  }
}

const highlightNode = (memberId) => {
  // Remove previous highlights
  d3.selectAll('.node').classed('highlighted', false)

  // Add highlight to the target node
  d3.selectAll('.node')
    .filter(d => d.data.id == memberId)
    .classed('highlighted', true)
    .transition()
    .duration(500)
    .style('opacity', 1)
    .transition()
    .duration(500)
    .style('opacity', null) // Return to normal
}

const clearHighlight = () => {
  highlightedMember.value = null
  d3.selectAll('.node').classed('highlighted', false)
}

const clearSearch = () => {
  searchQuery.value = ''
  searchResults.value = []
  clearHighlight()
}
</script>

<style>
.family-tree-page {
  min-height: 100vh;
  background: #f8f9fa;
}

.family-tree-content {
  padding: 20px;
  max-width: 100%;
}

.tree-header {
  text-align: center;
  margin-bottom: 30px;
  background: white;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.tree-header h1 {
  color: #2c3e50;
  font-size: 2.5rem;
  margin-bottom: 10px;
  font-weight: 700;
}

.tree-header p {
  color: #6c757d;
  font-size: 1.1rem;
  max-width: 500px;
  margin: 0 auto;
  line-height: 1.6;
}

.tree-controls {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.control-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: white;
  color: #495057;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.control-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  background: #f8f9fa;
  border-color: #dee2e6;
}

.btn-icon {
  font-size: 16px;
}

#tree-container {
  width: 100%;
  height: calc(100vh - 250px);
  min-height: 500px;
  background: var(--bg-tree);
  border-radius: 16px;
  border: 2px solid rgba(217, 177, 130, 0.3);
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(107, 79, 63, 0.1);
}

#tree-container svg {
  width: 100%;
  height: 100%;
  background: transparent;
}

.loading {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: #6c757d;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e9ecef;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.no-data {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  color: #6c757d;
  text-align: center;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.7;
  color: #dee2e6;
}

.no-data h3 {
  margin: 0 0 10px 0;
  font-size: 1.5rem;
  color: #495057;
}

.no-data p {
  margin: 0;
  font-size: 1rem;
  opacity: 0.8;
}

/* OrgChart styling */
.orgchart-container {
  background: transparent !important;
}

.orgchart .node {
  border-radius: 10px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
}

.orgchart .node.male {
  background-color: #e3f2fd !important;
  border-color: #2196f3 !important;
}

.orgchart .node.female {
  background-color: #fce4ec !important;
  border-color: #e91e63 !important;
}

.orgchart .node.unknown {
  background-color: #f5f5f5 !important;
  border-color: #9e9e9e !important;
}

.orgchart .node .title {
  font-weight: bold !important;
  font-size: 14px !important;
}

.orgchart .node .content {
  padding: 8px !important;
}

.orgchart .node img {
  border-radius: 50% !important;
  width: 40px !important;
  height: 40px !important;
  object-fit: cover !important;
}

/* Desktop styles */
@media (min-width: 1024px) {
  .family-tree-content {
    padding: 40px;
  }

  .tree-header h1 {
    font-size: 3rem;
  }

  .tree-header p {
    font-size: 1.2rem;
  }

  .tree-controls {
    gap: 20px;
    margin-bottom: 30px;
  }

  .control-btn {
    padding: 15px 25px;
    font-size: 16px;
  }

  #tree-container {
    height: calc(100vh - 300px);
    min-height: 600px;
  }

  .link {
    stroke-width: 4px;
  }

  .single-node circle {
    r: 30px;
  }

  .single-node text {
    font-size: 14px;
    dy: 50px;
  }

}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .family-tree-content {
    padding: 30px 20px;
  }

  .tree-header h1 {
    font-size: 2.2rem;
  }

  .tree-controls {
    gap: 15px;
    margin-bottom: 25px;
  }

  .control-btn {
    padding: 12px 18px;
    font-size: 14px;
  }

  #tree-container {
    height: calc(100vh - 280px);
    min-height: 450px;
  }

  .link {
    stroke-width: 3px;
  }

  .node circle {
    r: 25px;
  }

  .node text {
    font-size: 12px;
    dy: 40px;
  }
}

/* Search Section Styles */
.search-section {
  margin-bottom: 30px;
}

.search-container {
  max-width: 600px;
  margin: 0 auto;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  border-radius: 25px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.search-input-wrapper:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
}

.search-input {
  flex: 1;
  padding: 12px 16px;
  border: none;
  outline: none;
  font-size: 16px;
  background: transparent;
  color: var(--text-primary);
}

.search-input::placeholder {
  color: var(--text-secondary);
}

.search-btn, .clear-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: white;
  color: #495057;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  background: #f8f9fa;
  border-color: #dee2e6;
  color: var(--primary);
}

.clear-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  background: #f8f9fa;
  border-color: #dee2e6;
  color: #dc3545;
}

.search-results {
  margin-top: 16px;
  background: white;
  border-radius: 12px;
  border: 1px solid rgba(107, 79, 63, 0.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.results-header {
  padding: 12px 16px;
  background: rgba(107, 79, 63, 0.05);
  border-bottom: 1px solid rgba(107, 79, 63, 0.1);
}

.results-header span {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.results-list {
  max-height: 300px;
  overflow-y: auto;
}

.result-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(107, 79, 63, 0.05);
  cursor: pointer;
  transition: all 0.3s ease;
}

.result-item:hover {
  background: rgba(107, 79, 63, 0.03);
}

.result-item.active {
  background: rgba(107, 79, 63, 0.1);
}

.result-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden;
  margin-right: 12px;
  flex-shrink: 0;
}

.result-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  background: var(--bg-secondary);
}

.result-info {
  flex: 1;
  min-width: 0;
}

.result-name {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 2px;
  font-size: 14px;
}

.result-details {
  display: flex;
  gap: 8px;
  align-items: center;
  font-size: 12px;
  color: var(--text-secondary);
}

.generation {
  background: rgba(107, 79, 63, 0.1);
  color: var(--primary);
  padding: 2px 6px;
  border-radius: 8px;
  font-weight: 500;
}

.spouse-note {
  color: var(--accent);
  font-style: italic;
  font-weight: 500;
}

.focus-btn {
  background: var(--primary);
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.3s ease;
}

.focus-btn:hover {
  background: var(--secondary);
  transform: scale(1.05);
}

.no-results {
  margin-top: 16px;
  padding: 16px;
  background: rgba(244, 67, 54, 0.1);
  border: 1px solid rgba(244, 67, 54, 0.2);
  border-radius: 8px;
  text-align: center;
  color: #dc3545;
}

.no-results-icon {
  font-size: 24px;
  margin-bottom: 8px;
}

/* Highlighted node styles */
.node.highlighted circle {
  stroke: #ff6b35 !important;
  stroke-width: 4px !important;
  filter: drop-shadow(0 0 8px rgba(255, 107, 53, 0.6));
}

.node.highlighted text {
  fill: #ff6b35 !important;
  font-weight: bold !important;
}

.highlight-btn {
  background: linear-gradient(135deg, #ff6b35, #f7931e) !important;
  color: white !important;
}

/* Mobile styles */
@media (max-width: 767px) {
  .family-tree-page {
    padding-top: 60px; /* Account for fixed nav */
  }

  .family-tree-content {
    padding: 15px;
  }

  .tree-header {
    margin-bottom: 20px;
  }

  .tree-header h1 {
    font-size: 1.8rem;
  }

  .tree-header p {
    font-size: 0.9rem;
  }

  .search-section {
    margin-bottom: 20px;
  }

  .search-container {
    max-width: 100%;
  }

  .search-input-wrapper {
    border-radius: 20px;
  }

  .search-input {
    padding: 10px 14px;
    font-size: 16px; /* Prevent zoom on iOS */
  }

  .search-btn, .clear-btn {
    padding: 10px 12px;
  }

  .result-item {
    padding: 10px 12px;
  }

  .result-avatar {
    width: 35px;
    height: 35px;
    margin-right: 10px;
  }

  .result-name {
    font-size: 13px;
  }

  .result-details {
    font-size: 11px;
  }

  .focus-btn {
    padding: 5px 10px;
    font-size: 11px;
  }

  .tree-controls {
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
  }

  .control-btn {
    width: 160px;
    padding: 12px 20px;
    font-size: 14px;
    justify-content: center;
  }

  .control-btn span:last-child {
    display: none;
  }

  #tree-container {
    height: calc(100vh - 350px); /* Adjusted for search section */
    min-height: 350px;
    border-radius: 12px;
  }

  .loading p,
  .no-data h3,
  .no-data p {
    font-size: 14px;
    color: #6c757d;
  }

  .empty-icon {
    font-size: 3rem;
    color: #dee2e6;
  }

  .link {
    stroke-width: 2px;
  }

  .single-node circle {
    r: 20px;
  }

  .single-node text {
    font-size: 11px;
    dy: 35px;
  }

}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .search-input {
    font-size: 16px;
  }

  #tree-container {
    height: calc(100vh - 320px); /* Adjusted for search section */
  }

  .results-list {
    max-height: 250px;
  }
}
</style>
