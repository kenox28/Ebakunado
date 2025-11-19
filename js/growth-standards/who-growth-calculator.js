/**
 * WHO Child Growth Standards Calculator
 * Based on NNC EOPT (Electronic Operation Timbang Plus) standards
 * Implements Weight-for-Age, Length-for-Age, and Weight-for-Length classifications
 */

class GrowthStandardsCalculator {
	constructor() {
		this.standards = {
			weightForAge: {
				boys: null,
				girls: null,
			},
			lengthForAge: {
				boys: null,
				girls: null,
			},
			weightForLength: {
				boys: null,
				girls: null,
			},
		};
		this.loaded = false;
	}

	/**
	 * Load all growth standards data files
	 */
	async loadStandards() {
		if (this.loaded) return;

		try {
			// Load Weight-for-Age standards
			const wfaBoys = await fetch(
				"../../data/growth-standards/weight-for-age-boys.json"
			);
			this.standards.weightForAge.boys = await wfaBoys.json();

			// Load girls data if available
			try {
				const wfaGirls = await fetch(
					"../../data/growth-standards/weight-for-age-girls.json"
				);
				this.standards.weightForAge.girls = await wfaGirls.json();
			} catch (e) {
				console.warn("Weight-for-Age girls standards not yet available");
			}

			// Load Length-for-Age standards (if available)
			try {
				const lfaBoys = await fetch(
					"../../data/growth-standards/length-for-age-boys.json"
				);
				this.standards.lengthForAge.boys = await lfaBoys.json();
			} catch (e) {
				console.warn("Length-for-Age boys standards not yet available");
			}

			try {
				const lfaGirls = await fetch(
					"../../data/growth-standards/length-for-age-girls.json"
				);
				this.standards.lengthForAge.girls = await lfaGirls.json();
			} catch (e) {
				console.warn("Length-for-Age girls standards not yet available");
			}

			// Load Weight-for-Length standards (if available)
			try {
				const wflBoys = await fetch(
					"../../data/growth-standards/weight-for-length-boys.json"
				);
				this.standards.weightForLength.boys = await wflBoys.json();
			} catch (e) {
				console.warn("Weight-for-Length boys standards not yet available");
			}

			try {
				const wflGirls = await fetch(
					"../../data/growth-standards/weight-for-length-girls.json"
				);
				this.standards.weightForLength.girls = await wflGirls.json();
			} catch (e) {
				console.warn("Weight-for-Length girls standards not yet available");
			}

			this.loaded = true;
		} catch (error) {
			console.error("Error loading growth standards:", error);
			throw error;
		}
	}

	/**
	 * Calculate age in months from birth date
	 */
	calculateAgeInMonths(birthDate) {
		if (!birthDate) return null;

		const birth = new Date(birthDate);
		const today = new Date();
		const years = today.getFullYear() - birth.getFullYear();
		const months = today.getMonth() - birth.getMonth();
		const days = today.getDate() - birth.getDate();

		let totalMonths = years * 12 + months;

		// If the day hasn't occurred this month, subtract one month
		if (days < 0) {
			totalMonths--;
		}

		return Math.max(0, totalMonths);
	}

	/**
	 * Classify Weight-for-Age
	 * @param {number} ageMonths - Age in months
	 * @param {number} weightKg - Weight in kilograms
	 * @param {string} gender - 'boys' or 'girls'
	 * @returns {object} Classification result
	 */
	classifyWeightForAge(ageMonths, weightKg, gender) {
		if (!ageMonths && ageMonths !== 0) return null;
		if (!weightKg) return null;
		if (!gender) return null;

		const normalizedGender = this._normalizeGender(gender);
		if (!normalizedGender) return null;

		const standards = this.standards.weightForAge[normalizedGender];
		if (!standards || !standards.standards) {
			console.warn(`Weight-for-Age standards not available for ${gender}`);
			return null;
		}

		const ageKey = String(Math.floor(ageMonths));
		const ageData = standards.standards[ageKey];

		if (!ageData) {
			// Try to find closest age if exact match not found
			const availableAges = Object.keys(standards.standards)
				.map(Number)
				.sort((a, b) => a - b);
			const closestAge =
				availableAges.find((age) => age >= ageMonths) ||
				availableAges[availableAges.length - 1];
			const closestAgeKey = String(closestAge);
			const closestData = standards.standards[closestAgeKey];

			if (!closestData) return null;

			return this._classifyWeight(weightKg, closestData);
		}

		return this._classifyWeight(weightKg, ageData);
	}

	/**
	 * Internal method to classify weight based on thresholds
	 */
	_classifyWeight(weightKg, ageData) {
		if (weightKg <= ageData.severely_underweight.max) {
			return {
				status: "severely_underweight",
				label: "Severely Underweight",
				color: "red",
				icon: "🔴",
			};
		}

		if (
			weightKg >= ageData.underweight.min &&
			weightKg <= ageData.underweight.max
		) {
			return {
				status: "underweight",
				label: "Underweight",
				color: "orange",
				icon: "🟠",
			};
		}

		if (weightKg >= ageData.normal.min && weightKg <= ageData.normal.max) {
			return {
				status: "normal",
				label: "Normal",
				color: "green",
				icon: "🟢",
			};
		}

		// Above normal range
		if (weightKg > ageData.normal.max) {
			return {
				status: "overweight",
				label: "Overweight",
				color: "yellow",
				icon: "🟡",
			};
		}

		// Between severely underweight and underweight
		if (
			weightKg > ageData.severely_underweight.max &&
			weightKg < ageData.underweight.min
		) {
			return {
				status: "underweight",
				label: "Underweight",
				color: "orange",
				icon: "🟠",
			};
		}

		// Between underweight and normal
		if (weightKg > ageData.underweight.max && weightKg < ageData.normal.min) {
			return {
				status: "underweight",
				label: "Underweight",
				color: "orange",
				icon: "🟠",
			};
		}

		return null;
	}

	/**
	 * Classify Length-for-Age (for ages 0-11 months)
	 * @param {number} ageMonths - Age in months
	 * @param {number} lengthCm - Length in centimeters
	 * @param {string} gender - 'boys' or 'girls'
	 * @returns {object} Classification result
	 */
	classifyLengthForAge(ageMonths, lengthCm, gender) {
		if (ageMonths > 11) return null; // Only for 0-11 months
		if (!ageMonths && ageMonths !== 0) return null;
		if (!lengthCm) return null;
		if (!gender) return null;

		const normalizedGender = this._normalizeGender(gender);
		if (!normalizedGender) return null;

		const standards = this.standards.lengthForAge[normalizedGender];
		if (!standards || !standards.standards) {
			console.warn(`Length-for-Age standards not available for ${gender}`);
			return null;
		}

		const ageKey = String(Math.floor(ageMonths));
		const ageData = standards.standards[ageKey];

		if (!ageData) return null;

		return this._classifyLength(lengthCm, ageData);
	}

	/**
	 * Internal method to classify length based on thresholds
	 */
	_classifyLength(lengthCm, ageData) {
		if (ageData.severely_stunted && lengthCm <= ageData.severely_stunted.max) {
			return {
				status: "severely_stunted",
				label: "Severely Stunted",
				color: "red",
				icon: "🔴",
			};
		}

		if (
			ageData.normal &&
			lengthCm >= ageData.normal.min &&
			lengthCm <= ageData.normal.max
		) {
			return {
				status: "normal",
				label: "Normal",
				color: "green",
				icon: "🟢",
			};
		}

		if (ageData.tall && lengthCm >= ageData.tall.min) {
			return {
				status: "tall",
				label: "Tall",
				color: "blue",
				icon: "🔵",
			};
		}

		return null;
	}

	/**
	 * Classify Weight-for-Length
	 * @param {number} lengthCm - Length in centimeters
	 * @param {number} weightKg - Weight in kilograms
	 * @param {string} gender - 'boys' or 'girls'
	 * @returns {object} Classification result
	 */
	classifyWeightForLength(lengthCm, weightKg, gender) {
		if (!lengthCm) return null;
		if (!weightKg) return null;
		if (!gender) return null;

		const normalizedGender = this._normalizeGender(gender);
		if (!normalizedGender) return null;

		const standards = this.standards.weightForLength[normalizedGender];
		if (!standards || !standards.standards) {
			console.warn(`Weight-for-Length standards not available for ${gender}`);
			return null;
		}

		// Find the closest length range
		const lengthKeys = Object.keys(standards.standards)
			.map(Number)
			.sort((a, b) => a - b);
		const closestLength = lengthKeys.reduce((prev, curr) => {
			return Math.abs(curr - lengthCm) < Math.abs(prev - lengthCm)
				? curr
				: prev;
		});

		const lengthKey = String(closestLength);
		const lengthData = standards.standards[lengthKey];

		if (!lengthData) return null;

		return this._classifyWeightForLength(weightKg, lengthData);
	}

	/**
	 * Internal method to classify weight-for-length
	 */
	_classifyWeightForLength(weightKg, lengthData) {
		if (lengthData.sam && weightKg <= lengthData.sam.max) {
			return {
				status: "sam",
				label: "SAM (Severe Acute Malnutrition)",
				color: "red",
				icon: "🔴",
			};
		}

		if (
			lengthData.mam &&
			weightKg >= lengthData.mam.min &&
			weightKg <= lengthData.mam.max
		) {
			return {
				status: "mam",
				label: "MAM (Moderate Acute Malnutrition)",
				color: "orange",
				icon: "🟠",
			};
		}

		if (
			lengthData.normal &&
			weightKg >= lengthData.normal.min &&
			weightKg <= lengthData.normal.max
		) {
			return {
				status: "normal",
				label: "Normal",
				color: "green",
				icon: "🟢",
			};
		}

		if (
			lengthData.overweight &&
			weightKg >= lengthData.overweight.min &&
			weightKg <= lengthData.overweight.max
		) {
			return {
				status: "overweight",
				label: "Overweight",
				color: "yellow",
				icon: "🟡",
			};
		}

		if (lengthData.obese && weightKg >= lengthData.obese.min) {
			return {
				status: "obese",
				label: "Obese",
				color: "red",
				icon: "🔴",
			};
		}

		return null;
	}

	/**
	 * Calculate all growth assessments
	 * @param {string} birthDate - Birth date (YYYY-MM-DD)
	 * @param {string} gender - 'boys' or 'girls' or 'Male'/'Female'
	 * @param {number} weightKg - Weight in kilograms
	 * @param {number} lengthCm - Length/Height in centimeters
	 * @returns {object} Complete assessment results
	 */
	async assessGrowth(birthDate, gender, weightKg, lengthCm) {
		await this.loadStandards();

		const ageMonths = this.calculateAgeInMonths(birthDate);
		if (ageMonths === null) {
			return { error: "Invalid birth date" };
		}

		// Normalize gender
		const normalizedGender = this._normalizeGender(gender);

		const assessment = {
			ageMonths: ageMonths,
			weightForAge: null,
			lengthForAge: null,
			weightForLength: null,
		};

		// Weight-for-Age assessment
		if (weightKg) {
			assessment.weightForAge = this.classifyWeightForAge(
				ageMonths,
				weightKg,
				normalizedGender
			);
		}

		// Length-for-Age assessment (only for 0-11 months)
		if (lengthCm && ageMonths <= 11) {
			assessment.lengthForAge = this.classifyLengthForAge(
				ageMonths,
				lengthCm,
				normalizedGender
			);
		}

		// Weight-for-Length assessment
		if (weightKg && lengthCm) {
			assessment.weightForLength = this.classifyWeightForLength(
				lengthCm,
				weightKg,
				normalizedGender
			);
		}

		return assessment;
	}

	/**
	 * Normalize gender string
	 */
	_normalizeGender(gender) {
		if (!gender) return null;
		const g = gender.toLowerCase();
		if (g === "male" || g === "boy" || g === "boys" || g === "m") return "boys";
		if (g === "female" || g === "girl" || g === "girls" || g === "f")
			return "girls";
		return null;
	}
}

// Create global instance
const growthCalculator = new GrowthStandardsCalculator();


