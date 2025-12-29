<template>
  <div class="family-tree-page">
    <Navigation />

    <div class="family-tree-content">
      <div class="tree-header">
        <h1>Pohon Keluarga</h1>
        <p>Visualisasi interaktif silsilah keluarga dengan fitur zoom dan pan</p>
      </div>

      <div class="tree-controls">
        <button @click="zoomIn" class="control-btn">
          <span class="btn-icon">🔍</span>
          <span>Zoom In</span>
        </button>
        <button @click="zoomOut" class="control-btn">
          <span class="btn-icon">🔎</span>
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

const getGenerationName = (generation) => {
  const generationNames = {
    0: 'Keluarga',
    1: 'Pendiri', // Leluhur/Root generation
    2: 'Penerus', // Successor/Children generation
    3: 'Cucu',    // Grandchildren generation
    4: 'Buyut',   // Great-grandchildren generation
    5: 'Canggah'  // Great-great-grandchildren generation
  }
  return generationNames[generation] || `Gen ${generation}`
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
    height: calc(100vh - 280px);
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
</style>
