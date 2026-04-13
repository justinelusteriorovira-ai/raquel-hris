/**
 * Employee Form JS — 12-step wizard + dynamic repeater rows
 */

const TOTAL_STEPS = 12;

function showStep(step) {
    document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.step-wizard .step').forEach(el => {
        el.classList.remove('active', 'completed');
    });
    const target = document.getElementById('step' + step);
    if (target) target.style.display = 'block';
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const label = document.getElementById('step' + i + 'Label');
        if (label) {
            if (i < step) label.classList.add('completed');
            else if (i === step) label.classList.add('active');
        }
    }
    // Scroll wizard to show active step
    const activeLabel = document.getElementById('step' + step + 'Label');
    if (activeLabel) activeLabel.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Copy residential address to permanent
function copyResAddress() {
    const fields = ['house_no','street','subdivision','barangay','city','province','zip_code'];
    fields.forEach(f => {
        const src = document.querySelector('[name="res_' + f + '"]');
        const dst = document.getElementById('perm_' + f);
        if (src && dst) dst.value = src.value;
    });
}

// Toggle disclosure detail areas
function toggleDetails(checkbox, detailsDivId) {
    const div = document.getElementById(detailsDivId);
    if (div) {
        div.classList.toggle('show', checkbox.checked);
    }
}

// Generic repeater: child/sibling (4-column: surname, first, middle, dob)
function addRepeaterRow(containerId, prefix) {
    const c = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="${prefix}_surname[]" placeholder="Surname"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="${prefix}_first_name[]" placeholder="First Name"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="${prefix}_middle_name[]" placeholder="Middle Name"></div>
            <div class="col-md-3 mb-2"><input type="date" class="form-control form-control-sm" name="${prefix}_dob[]"></div>
        </div>`;
    c.appendChild(div);
}

// Education row
function addEducationRow() {
    const c = document.getElementById('educationContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><select class="form-select form-select-sm" name="edu_level[]"><option value="Elementary">Elementary</option><option value="Secondary">Secondary</option><option value="Vocational">Vocational</option><option value="College" selected>College</option><option value="Graduate Studies">Graduate</option></select></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_school[]" placeholder="School Name"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_degree[]" placeholder="Degree/Course"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="edu_from[]"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="edu_to[]"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_units[]" placeholder="Highest Level/Units"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="edu_year_grad[]" placeholder="Year Grad"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_honors[]" placeholder="Honors"></div>
        </div>`;
    c.appendChild(div);
}

// Work experience row
function addWorkRow() {
    const c = document.getElementById('workContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="work_from[]"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="work_to[]"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_title[]" placeholder="Job Title"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_company[]" placeholder="Company"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="work_salary[]" placeholder="Salary"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_status[]" placeholder="Status"></div>
            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="work_reason[]" placeholder="Reason for Leaving"></div>
        </div>`;
    c.appendChild(div);
}

// Training row
function addTrainingRow() {
    const c = document.getElementById('trainingContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="training_from[]"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="training_to[]"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="training_title[]" placeholder="Training Title"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="training_type[]" placeholder="Type"></div>
            <div class="col-md-1 mb-2"><input type="number" class="form-control form-control-sm" name="training_hours[]" placeholder="Hrs"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="training_conducted[]" placeholder="Conducted By"></div>
        </div>`;
    c.appendChild(div);
}

// Voluntary work row
function addVoluntaryRow() {
    const c = document.getElementById('voluntaryContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="vol_from[]"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="vol_to[]"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_org[]" placeholder="Organization"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_address[]" placeholder="Address"></div>
            <div class="col-md-1 mb-2"><input type="number" class="form-control form-control-sm" name="vol_hours[]" placeholder="Hrs"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_position[]" placeholder="Position/Nature"></div>
        </div>`;
    c.appendChild(div);
}

// Eligibility row
function addEligibilityRow() {
    const c = document.getElementById('eligibilityContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="elig_title[]" placeholder="License/Cert Title"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="elig_from[]"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="elig_to[]"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="elig_number[]" placeholder="License No."></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="elig_exam_date[]"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="elig_exam_place[]" placeholder="Place of Exam"></div>
        </div>`;
    c.appendChild(div);
}

// Simple single-field row (skills, recognitions, memberships)
function addSimpleRow(containerId, fieldName, placeholder) {
    const c = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <input type="text" class="form-control form-control-sm" name="${fieldName}[]" placeholder="${placeholder}">`;
    c.appendChild(div);
}

// Real property row
function addRealPropertyRow() {
    const c = document.getElementById('realPropContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_desc[]" placeholder="Description"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_kind[]" placeholder="Kind"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_location[]" placeholder="Location"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_assessed[]" placeholder="Assessed Value"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_market[]" placeholder="Market Value"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_acq_mode[]" placeholder="Year-Mode"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_acq_cost[]" placeholder="Acq. Cost"></div>
        </div>`;
    c.appendChild(div);
}

// Personal property row
function addPersonalPropertyRow() {
    const c = document.getElementById('personalPropContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-5 mb-2"><input type="text" class="form-control form-control-sm" name="pprop_desc[]" placeholder="Description"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="pprop_year[]" placeholder="Year Acquired"></div>
            <div class="col-md-4 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="pprop_cost[]" placeholder="Acquisition Cost"></div>
        </div>`;
    c.appendChild(div);
}

// Liability row
function addLiabilityRow() {
    const c = document.getElementById('liabilitiesContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="liab_nature[]" placeholder="Nature of Liability"></div>
            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="liab_creditor[]" placeholder="Name of Creditor"></div>
            <div class="col-md-4 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="liab_balance[]" placeholder="Outstanding Balance"></div>
        </div>`;
    c.appendChild(div);
}

// Profile image preview
function previewImage(input) {
    const preview = document.getElementById('profilePreview');
    const container = document.getElementById('profilePreviewContainer');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        // Only hide if it's completely empty (not even an existing image)
        if (!preview.getAttribute('src')) {
            container.style.display = 'none';
        }
    }
}

// Automatically navigate to the step containing an invalid required field
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('invalid', function (e) {
            const stepContent = e.target.closest('.step-content');
            if (stepContent) {
                const stepId = stepContent.id;
                const stepNum = parseInt(stepId.replace('step', ''), 10);
                if (!isNaN(stepNum)) {
                    showStep(stepNum);
                }
            }
        }, true); // Use capture phase
    }

    // Toggle contract dates visibility
    const statusSelect = document.querySelector('select[name="employment_status"]');
    const contractDatesRow = document.getElementById('contractDatesRow');
    if (statusSelect && contractDatesRow) {
        const checkStatus = () => {
            if (['Probationary', 'Contractual'].includes(statusSelect.value)) {
                contractDatesRow.style.display = 'flex';
            } else {
                contractDatesRow.style.display = 'none';
            }
        };
        statusSelect.addEventListener('change', checkStatus);
        // Run once on load for edit mode
        checkStatus();
    }
});
