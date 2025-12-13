<template>

  <Head title="Report 9.1" />
  <PageBar>
    <p class="m-0 fs-4">Report 9.1</p>
  </PageBar>

  <Refresh>
    <Button class="nav-btn" icon="bi bi-file-earmark-pdf-fill" @click="openPdf" />
    <Button class="nav-btn" title="refresh" icon='bi bi-arrow-repeat'
      @click="refresh(route().params.client_id, route().params.monthYear)" />
    <Link title="back" :href="route('report-client-data-list', { client_id: route().params.client_id })"
      class="nav-btn">
      <i class="bi bi-chevron-left"></i>
    </Link>
  </Refresh>

  <div class="card card-body border p-lg-5">
    <div class="row">
      <div class="col-12 text-center position-relative">
        <img :src="logo" width="90" alt="bd_logo" class="fw-bold position-absolute start-0 ms-2" style="top: 25%">
        <label class="fw-bold border border-secondary border-2 position-absolute end-0 p-2 me-2">Mushak 9.1</label>
        <h5 class="mb-0">GOVERNMENT OF THE PEOPLE'S REPUBLIC OF BANGLADESH</h5>
        <h5 class="mb-0">NATIONAL BOARD OF REVENUE</h5>
      </div>
    </div>
    <div class="row">
      <div class="col-12 text-center">
        <h5 class="mb-0">VALUE ADDED TAX RETURN</h5>
        <p class="mb-0">[See rule 47(1)]</p>
        <p class="mb-0">[Please read the instruction before filling up this form]</p>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center">PART - 1: TAXPAYER'S INFORMATION</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <tbody>
              <tr>
                <td>1. BIN</td>
                <td>{{ props.report91?.client?.bin }}</td>
              </tr>
              <tr>
                <td>2. Name of Taxpayer</td>
                <td>{{ props.report91?.client?.company_name }}</td>
              </tr>
              <tr>
                <td>3. Address of Taxpayer</td>
                <td>{{ props.report91?.client?.address }}</td>
              </tr>
              <tr>
                <td>4. Type of Ownership</td>
                <td>Proprietorship/Partnership/Private Limited/Public Limited</td>
              </tr>
              <tr>
                <td>5. Economic Activity</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 2: RETURN SUBMISSION DATA</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <tbody>
              <tr>
                <td>1. Tax Period</td>
                <td>{{ moment(route().params.monthYear).format('MMMM YYYY') }}</td>
              </tr>
              <tr>
                <td>2. Type of Return</td>
                <td>A) Main Return (Section 64)</td>
              </tr>
              <tr>
                <td colspan="2">[Please select your desired option]</td>
              </tr>
              <tr>
                <td>Reason for Amendment</td>
                <td></td>
              </tr>
              <tr>
                <td rowspan="2">3. Any activities in this Tax Period? </td>
                <td>
                  <div class="input-group">
                    <div class="form-check me-3">
                      <input class="form-check-input" type="radio" name="activities" id="activities-yes" checked>
                      <label class="form-check-label" for="activities-yes">
                        Yes
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="activities" id="activities-no">
                      <label class="form-check-label" for="activities-no">
                        No
                      </label>
                    </div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>[If Selected “No” Please Fill Only the Relevant Part]</td>
              </tr>
              <tr>
                <td>4. Date of Submission</td>
                <td></td>
              </tr>
              <tr>
                <td>5. Last Amendment Date</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 3: SUPPLY - OUTPUT TAX</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead class="text-center">
              <tr>
                <th colspan="2" class="col-5">Nature of Supply</th>
                <th class="col-1">Note</th>
                <th>Value (a)</th>
                <th>SD (b)</th>
                <th>VAT (c)</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td rowspan="2" class="text-start">Zero Rated Goods/Service</td>
                <td class="text-start">Direct Export</td>
                <td>1</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Deemed Export</td>
                <td>2</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2" class="text-start">Exempted Goods/Service</td>
                <td>3</td>
                <td>{{ props.report91?.note3 }}</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2" class="text-start">Standard Rated Goods/Service</td>
                <td>4</td>
                <td>{{ props.report91?.note4a }}</td>
                <td>{{ props.report91?.note4b }}</td>
                <td>{{ props.report91?.note4c }}</td>
              </tr>
              <tr>
                <td colspan="2" class="text-start">Goods Based on MRP</td>
                <td>5</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2" class="text-start">Goods/Service Based on Specific VAT</td>
                <td>6</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2" class="text-start">Goods/Service Other than Standard Rate</td>
                <td>7</td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2" class="text-start">Retail/Wholesale/Trade Based Supply</td>
                <td>8</td>
                <td>{{ props.report91?.note8a }}</td>
                <td>{{ props.report91?.note8b }}</td>
                <td>{{ props.report91?.note8c }}</td>
              </tr>
              <tr class="fw-bold">
                <td colspan="2" class="text-start">Total Sales Value & Total Payable Taxes</td>
                <td>9</td>
                <td>{{ props.report91?.note9a }}</td>
                <td>{{ props.report91?.note9b }}</td>
                <td>{{ props.report91?.note9c }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 4: PURCHASE - INPUT TAX</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead class="text-center">
              <tr>
                <th colspan="2" class="col-5">Nature of Supply</th>
                <th class="col-1">Note</th>
                <th>Value (a)</th>
                <th>VAT (b)</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td rowspan="2" class="text-start">Zero Rated Goods/Service</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>10</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Import</td>
                <td>11</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td rowspan="2" class="text-start">Exempted Goods/Service</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>12</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Import</td>
                <td>13</td>
                <td>{{ props.report91?.note13a }}</td>
                <td>{{ props.report91?.note13b }}</td>
              </tr>
              <tr>
                <td rowspan="2" class="text-start">Standard Rated Goods/Service</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>14</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Import</td>
                <td>15</td>
                <td>{{ props.report91?.note15a }}</td>
                <td>{{ props.report91?.note15b }}</td>
              </tr>
              <tr>
                <td rowspan="2" class="text-start">Goods/Service Other than Standard Rate</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>16</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Import</td>
                <td>17</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Goods/Service Based on Specific VAT</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>18</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td rowspan="2" class="text-start">Goods/Service Not Admissible for Credit (Local Purchase)</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>19</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Import</td>
                <td>20</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td rowspan="2" class="text-start">Goods/Service Not Admissible for Credit (Taxpayers who sell only
                  Exempted/ Specific VAT and Goods/ Service Other than Standard Rate/ Credits not taken within
                  stipulated
                  time)</td>
                <td class="text-start col-2">Local Purchase</td>
                <td>21</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Import</td>
                <td>22</td>
                <td>{{ props.report91?.note22a }}</td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2" class="text-start fw-bold">Total Input Tax Credit</td>
                <td>23</td>
                <td>{{ props.report91?.note23a }}</td>
                <td>{{ props.report91?.note23b }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 5: INCREASING ADJUSTMENTS (VAT)</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead>
              <tr class="text-center">
                <th class="col-5 text-start">Adjustment Details</th>
                <th class="col-1">Note</th>
                <th>VAT Amount</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td class="text-start">Due to VAT Deducted at Source by the Supply Receiver</td>
                <td>24</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Payment Not Made Through Banking Channel</td>
                <td>25</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Issuance of Debit Note</td>
                <td>26</td>
                <td>{{ props.report91?.note26 }}</td>
              </tr>
              <tr>
                <td class="text-start">Any Other Adjustments</td>
                <td>27</td>
                <td>{{ props.report91?.note27 }}</td>
              </tr>
              <tr class="fw-bold">
                <td class="text-start">Total Increasing Adjustment</td>
                <td>28</td>
                <td>{{ props.report91?.note28 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 6: DECREASING ADJUSTMENTS (VAT)</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead>
              <tr class="text-center">
                <th class="col-5 text-start">Adjustment Details</th>
                <th class="col-1">Note</th>
                <th>VAT Amount</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td class="text-start">Due to VAT Deducted at Source by the Supplies Delivered</td>
                <td>29</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Advance Tax Paid at Import Stage</td>
                <td>30</td>
                <td>{{ props.report91?.note30 }}</td>
              </tr>
              <tr>
                <td class="text-start">Issuance of Credit Note</td>
                <td>31</td>
                <td>{{ props.report91?.note31 }}</td>
              </tr>
              <tr>
                <td class="text-start">Any Other Adjustments</td>
                <td>32</td>
                <td>{{ props.report91?.note32 }}</td>
              </tr>
              <tr class="fw-bold">
                <td class="text-start">Total Decreasing Adjustment</td>
                <td>33</td>
                <td>{{ props.report91?.note33 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 7: NET TAX CALCULATION</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead>
              <tr class="text-center">
                <th class="col-5 text-start">Items</th>
                <th class="col-1">Note</th>
                <th>Amount (Tax)</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td class="text-start">Net Payable VAT for the Tax Period (Section- 45) (9c-23b+28-33)</td>
                <td>34</td>
                <td>{{ props.report91?.note34 }}</td>
              </tr>
              <tr>
                <td class="text-start">Net Payable VAT for the Tax Period after Adjustment with Closing Balance and
                  balance of form 18.6 [34- (52+56)]</td>
                <td>35</td>
                <td>{{ props.report91?.note35 }}</td>
              </tr>
              <tr>
                <td class="text-start">Net Payable Supplementary Duty for the Tax Period (Before adjustment with
                  Closing
                  Balance) [9b+38- (39+40)]</td>
                <td>36</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Net Payable Supplementary Duty for the Tax Period after Adjusted with Closing
                  Balance and balance of form 18.6 [36-(53+57)]</td>
                <td>37</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Supplementary Duty Against Issuance of Debit Note</td>
                <td>38</td>
                <td>{{ props.report91?.note38 }}</td>
              </tr>
              <tr>
                <td class="text-start">Supplementary Duty Against Issuance of Credit Note</td>
                <td>39</td>
                <td>{{ props.report91?.note39 }}</td>
              </tr>
              <tr>
                <td class="text-start">Supplementary Duty Paid on Inputs Against Exports</td>
                <td>40</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Interest on Overdue VAT (Based on note 35)</td>
                <td>41</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Interest on Overdue SD (Based on note 37)</td>
                <td>42</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Fine/Penalty for Non-submission of Return</td>
                <td>43</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Other Fine/Penalty/Interest</td>
                <td>44</td>
                <td>{{ props.report91?.note44 }}</td>
              </tr>
              <tr>
                <td class="text-start">Payable Excise Duty</td>
                <td>45</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Payable Development Surcharge</td>
                <td>46</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Payable ICT Development Surcharge</td>
                <td>47</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Payable Health Care Surcharge</td>
                <td>48</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Payable Environmental Protection Surcharge</td>
                <td>49</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Net Payable VAT for Treasury Deposit (35+41+43+44)</td>
                <td>50</td>
                <td>{{ props.report91?.note50 }}</td>
              </tr>
              <tr>
                <td class="text-start">Net Payable SD for Treasury Deposit (37+42)</td>
                <td>51</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Closing Balance of Last Tax Period (VAT)</td>
                <td>52</td>
                <td>{{ props.report91?.note52 }}</td>
              </tr>
              <tr>
                <td class="text-start">Closing Balance of Last Tax Period (SD)</td>
                <td>53</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 8: ADJUSTMENT FOR OLD ACCOUNT CURRENT BALANCE</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead>
              <tr class="text-center">
                <th class="col-5 text-start">Items</th>
                <th class="col-1">Note</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td class="text-start">Remaining Balance (VAT) from Mushak-18.6, [Rule 118(5)]</td>
                <td>54</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Remaining Balance (SD) from Mushak-18.6, [Rule 118(5)]</td>
                <td>55</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Decreasing Adjustment for Note 54 (up to 30% of Note 34)</td>
                <td>56</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Decreasing Adjustment for Note 55 (up to 30% of Note 36)</td>
                <td>57</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 9: ACCOUNTS CODE WISE PAYMENT SCHEDULE (TREASURY DEPOSIT)</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead>
              <tr class="text-center">
                <th class="col-5 text-start">Items</th>
                <th class="col-1">Note</th>
                <th class="col-4">Amount Code</th>
                <th class="col-2">Amount</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td class="text-start">VAT Deposit for the Current Tax Period</td>
                <td>58</td>
                <td>1 / 1133 / 0018 / 0311</td>
                <td>{{ props.report91?.note58 }}</td>
              </tr>
              <tr>
                <td class="text-start">SD Deposit for the Current Tax Period</td>
                <td>59</td>
                <td>1 / 1133 / 0018 / 0711</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Excise Duty</td>
                <td>60</td>
                <td>1 / 1133 / 0018 / 0601</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Development Surcharge</td>
                <td>61</td>
                <td>1 / 1103 / 0000 / 2225</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">ICT Development Surcharge</td>
                <td>62</td>
                <td>1 / 1103 / 0000 / 2214</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Health Care Surcharge</td>
                <td>63</td>
                <td>1 / 1103 / 0000 / 2212</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Environmental Protection Surcharge</td>
                <td>64</td>
                <td>1 / 1103 / 0000 / 2213</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 10: CLOSING BALANCE</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <thead>
              <tr class="text-center">
                <th class="col-5 text-start">Items</th>
                <th class="col-1">Note</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <tr>
                <td class="text-start">Closing Balance (VAT) (58-50-67+Not Approved Amount of VAT in Refund Module (If
                  any))</td>
                <td>65</td>
                <td>{{ props.report91?.note65 }}</td>
              </tr>
              <tr>
                <td class="text-start">Closing Balance (SD) [59-51-68+Not Approved Amount of SD in Refund Module (If
                  any)]
                </td>
                <td>66</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 11: REFUND</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <tbody class="text-center">
              <tr>
                <td colspan="2">Remaining Balance (VAT) from Mushak-18.6, [Rule 118(5)]</td>
                <td>
                  <div class="input-group">
                    <div class="form-check me-3">
                      <input class="form-check-input" type="radio" name="balance" id="balance-yes" checked>
                      <label class="form-check-label" for="balance-yes">
                        Yes
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="balance" id="balance-no">
                      <label class="form-check-label" for="balance-no">
                        No
                      </label>
                    </div>
                  </div>
                </td>
              </tr>
              <tr class="text-center fw-bold">
                <td class="col-5 text-start">Items</td>
                <td class="col-1">Note</td>
                <td>Amount</td>
              </tr>
              <tr>
                <td class="text-start">Requested Amount for Refund (VAT)</td>
                <td>67</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Requested Amount for Refund (SD)</td>
                <td>68</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h5 class="text-center my-3">PART - 12: DECLARATION</h5>
        <div class="table-responsive">
          <table class="table table-sm table-bordered border-secondary align-middle">
            <tbody class="text-center">
              <tr>
                <td class="text-start">Name:</td>
                <td>{{ props.report91?.client?.client?.name }}</td>
              </tr>
              <tr>
                <td class="text-start">Designation:</td>
                <td>Owner/Manager/Managing Director/Chairman</td>
              </tr>
              <tr>
                <td class="text-start">Mobile Number:</td>
                <td>{{ props.report91?.client?.client?.mobile }}</td>
              </tr>
              <tr>
                <td class="text-start">National ID/Passport Number:</td>
                <td></td>
              </tr>
              <tr>
                <td class="text-start">Email:</td>
                <td>{{ props.report91?.client?.client?.email }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
          <label class="form-check-label" for="flexCheckDefault">
            I hereby declare that all information provided in this Return Form are complete, true & accurate. In case
            of
            any untrue/incomplete statement, I may be subjected to penal action under The Value Added Tax and
            Supplementary Duty Act, 2012 or any other applicable Act prevailing at present.
          </label>
        </div>
      </div>
    </div>
    <div class="row mt-5">
      <div class="col-12 text-end my-5">
        <p class="m-0 me-5">Signature</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, onMounted, onBeforeUnmount } from 'vue';
import { empty } from '../../../helpers/utils';
import { user, role } from '../../../composables/user';
import { Link, router } from '@inertiajs/vue3';

import logo from "@/assets/images/gov.png";
import Button from "../../../components/Button.vue";
import PageBar from '../../../components/PageBar.vue';
import Refresh from '../../../components/Refresh.vue';
import moment from 'moment-timezone';

const props = defineProps({
  report91: { type: Object },
  search: { type: String }
});

const notify = inject('showToast'); // toast

const openPdf = () => {
  if (typeof window !== 'undefined') {
    const id = route().params.id;
    window.open(route('report-91-pdf', { client_id: route().params.client_id, monthYear: route().params.monthYear }), '_blank');
  }
};

// reload summary 43
const refresh = (id, monthYear) => router.get(route('report-91', { client_id: id, monthYear: monthYear }), {}, { preserveScroll: true });

onMounted(() => {
  const reload = () => router.reload({ only: ['report91'], preserveState: true, preserveScroll: true });
  window.Echo.private(`App.Models.User.${user.id}`).listen('.CommonEvent', reload);
});

onBeforeUnmount(() => {
  window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>